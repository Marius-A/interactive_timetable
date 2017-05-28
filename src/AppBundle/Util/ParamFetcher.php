<?php


namespace AppBundle\Util;

use FOS\RestBundle\Controller\Annotations\ParamInterface;
use FOS\RestBundle\Exception\InvalidParameterException;
use FOS\RestBundle\Request\ParamFetcher as BaseParamFetcher;
use FOS\RestBundle\Request\ParamReaderInterface;
use FOS\RestBundle\Util\ResolverTrait;
use FOS\RestBundle\Validator\Constraints\ResolvableConstraintInterface;
use JsonSchema\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ParamFetcher extends BaseParamFetcher
{
    use ResolverTrait;

    private $container;
    private $parameterBag;
    private $requestStack;
    private $validator;

    /**
     * Initializes fetcher.
     *
     * @param ContainerInterface $container
     * @param ParamReaderInterface $paramReader
     * @param RequestStack $requestStack
     * @param ValidatorInterface $validator
     */
    public function __construct(ContainerInterface $container, ParamReaderInterface $paramReader, RequestStack $requestStack, ValidatorInterface $validator = null)
    {
        parent::__construct($container, $paramReader, $requestStack, $validator);

        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->validator = $validator;

        $this->parameterBag = new ParameterBag($paramReader);
    }

    /**
     * {@inheritdoc}
     */
    public function setController($controller)
    {
        $this->parameterBag->setController($this->getRequest(), $controller);
    }

    /**
     * Add additional params to the ParamFetcher during runtime.
     *
     * Note that adding a param that has the same name as an existing param will override that param.
     *
     * @param ParamInterface $param
     */
    public function addParam(ParamInterface $param)
    {
        $this->parameterBag->addParam($this->getRequest(), $param);
    }

    /**
     * @return ParamInterface[]
     */
    public function getParams()
    {
        return $this->parameterBag->getParams($this->getRequest());
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $strict = null)
    {
        $params = $this->getParams();

        if (!array_key_exists($name, $params)) {
            throw new \InvalidArgumentException(sprintf("No @ParamInterface configuration for parameter '%s'.", $name));
        }

        /** @var ParamInterface $param */
        $param = $params[$name];
        $default = $param->getDefault();
        $default = $this->resolveValue($this->container, $default);
        $strict = (null !== $strict ? $strict : $param->isStrict());

        $paramValue = $param->getValue($this->getRequest(), $default);

        return $this->cleanParamWithRequirements($param, $paramValue, $strict, $default);
    }

    /**
     * @param ParamInterface $param
     * @param mixed $paramValue
     * @param bool $strict
     * @param mixed $default
     *
     * @throws BadRequestHttpException
     * @throws \RuntimeException
     *
     * @return mixed
     *
     * @internal
     */
    protected function cleanParamWithRequirements(ParamInterface $param, $paramValue, $strict, $default)
    {
        $this->checkNotIncompatibleParams($param);
        if ($default !== null && $default === $paramValue) {
            return $paramValue;
        }

        $constraints = $param->getConstraints();
        $this->resolveConstraints($constraints);
        if (empty($constraints)) {
            return $paramValue;
        }
        if (null === $this->validator) {
            throw new \RuntimeException(
                'The ParamFetcher requirements feature requires the symfony/validator component.'
            );
        }

        try {
            $errors = $this->validator->validate($paramValue, $constraints);
        } catch (ValidatorException $e) {
            $violation = new ConstraintViolation(
                $e->getMessage(),
                $e->getMessage(),
                array(),
                $paramValue,
                '',
                null,
                null,
                $e->getCode()
            );
            $errors = new ConstraintViolationList(array($violation));
        }

        if (0 < count($errors)) {
            if ($strict) {
                throw InvalidParameterException::withViolations($param, $errors);
            }

            return null === $default ? '' : $default;
        }

        return $paramValue;
    }

    /**
     * {@inheritdoc}
     */
    public function all($strict = null)
    {
        $configuredParams = $this->getParams();

        $params = [];
        foreach ($configuredParams as $name => $param) {
            $params[$name] = $this->get($name, $strict);
        }

        return $params;
    }

    /**
     * Check if current param is not in conflict with other parameters
     * according to the "incompatibles" field.
     *
     * @param ParamInterface $param the configuration for the param fetcher
     *
     * @throws InvalidArgumentException
     * @throws BadRequestHttpException
     *
     * @internal
     */
    protected function checkNotIncompatibleParams(ParamInterface $param)
    {
        if (null === $param->getValue($this->getRequest(), null)) {
            return;
        }

        $params = $this->getParams();
        foreach ($param->getIncompatibilities() as $incompatibleParamName) {
            if (!array_key_exists($incompatibleParamName, $params)) {
                throw new \InvalidArgumentException(sprintf("No @ParamInterface configuration for parameter '%s'.", $incompatibleParamName));
            }
            $incompatibleParam = $params[$incompatibleParamName];

            if (null !== $incompatibleParam->getValue($this->getRequest(), null)) {
                $exceptionMessage = sprintf(
                    "'%s' param is incompatible with %s param.",
                    $param->getName(),
                    $incompatibleParam->getName()
                );

                throw new BadRequestHttpException($exceptionMessage);
            }
        }
    }

    /**
     * @param Constraint[] $constraints
     */
    private function resolveConstraints(array $constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof ResolvableConstraintInterface) {
                $constraint->resolve($this->container);
            }
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return Request
     */
    private function getRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new \RuntimeException('There is no current request.');
        }

        return $request;
    }
}