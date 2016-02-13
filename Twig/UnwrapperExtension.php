<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Twig;


use AlexeyKupershtokh\ThriftDoctrineBundle\Unwrapper\Unwrapper;

class UnwrapperExtension extends \Twig_Extension
{
    protected $unwrapper;

    public function __construct($dm)
    {
        $this->unwrapper = new Unwrapper($dm);
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('unwrap_persistent_collections', array($this, 'unwrap')),
        );
    }

    /**
     * @param $doc
     */
    public function unwrap($doc)
    {
        $this->unwrapper->cascadeRemovePersistentCollections($doc);
        return $doc;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "unwrapper_extension";
    }
}
