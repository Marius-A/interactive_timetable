<?php


namespace AppBundle\Service\Traits;


use Symfony\Component\Translation\Translator;

/**
 * Trait TranslatorTrait
 * @package AppBundle\Service\Traits
 */
trait TranslatorTrait
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param Translator $translator
     * @return TranslatorTrait
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
        return $this;
    }
}