<?php

namespace AppBundle\Util;

use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\Controller\Annotations\ParamInterface;
use FOS\RestBundle\Request\ParamReaderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ParameterBag
 * @package AppBundle\Util
 */
final class ParameterBag
{
    private $paramReader;
    private $params = array();

    public function __construct(ParamReaderInterface $paramReader)
    {
        $this->paramReader = $paramReader;
    }

    public function getParams(Request $request)
    {
        $requestId = spl_object_hash($request);
        if (!isset($this->params[$requestId]) || empty($this->params[$requestId]['controller'])) {
            throw new \InvalidArgumentException('Controller and method needs to be set via setController.');
        }
        if ($this->params[$requestId]['params'] === null) {
            return $this->initParams($requestId);
        }

        return $this->params[$requestId]['params'];
    }

    public function addParam(Request $request, ParamInterface $param)
    {
        $requestId = spl_object_hash($request);
        $this->getParams($request);

        $this->params[$requestId]['params'][$param->getName()] = $param;
    }

    public function setController(Request $request, $controller)
    {
        $requestId = spl_object_hash($request);
        $this->params[$requestId] = array(
            'controller' => $controller,
            'params' => null,
        );
    }

    /**
     * Initialize the parameters.
     *
     * @param string $requestId
     *
     * @return ParamInterface[]
     * @throws \InvalidArgumentException
     */
    private function initParams($requestId)
    {
        $controller = $this->params[$requestId]['controller'];
        if (!is_array($controller) || empty($controller[0]) || !is_object($controller[0])) {
            throw new \InvalidArgumentException(
                'Controller needs to be set as a class instance (closures/functions are not supported)'
            );
        }

        return $this->params[$requestId]['params'] = $this->paramReader->read(
            new \ReflectionClass(ClassUtils::getClass($controller[0])),
            $controller[1]
        );
    }
}
