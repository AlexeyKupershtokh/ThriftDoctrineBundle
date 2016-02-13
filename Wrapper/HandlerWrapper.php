<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Wrapper;

use AlexeyKupershtokh\ThriftDoctrineBundle\Unwrapper\Unwrapper;
use Doctrine\ODM\MongoDB\DocumentManager;

class HandlerWrapper
{
    protected $hander;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @param $handler
     */
    public function __construct($handler)
    {
        $this->hander = $handler;
    }

    /**
     * @param DocumentManager $dm
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function __call($name, $arguments)
    {
        $result = call_user_func_array(array($this->hander, $name), $arguments);
        $unwrapper = new Unwrapper($this->dm);
        if (is_object($result)) {
            $unwrapper->cascadeRemovePersistentCollections($result);
        } elseif (is_array($result)) {
            foreach ($result as $eachResult) {
                $unwrapper->cascadeRemovePersistentCollections($eachResult);
            }
        }
        return $result;
    }
}
