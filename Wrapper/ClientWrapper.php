<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Wrapper;

use AlexeyKupershtokh\ThriftDoctrineBundle\Unwrapper\Unwrapper;
use Doctrine\ODM\MongoDB\DocumentManager;

class ClientWrapper
{
    protected $client;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = $client;
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
        if (method_exists($this->client, 'send_' . $name)) {
            $unwrapper = new Unwrapper($this->dm);
            foreach ($arguments as $k => $argument) {
                if (is_object($argument)) {
                    $unwrapper->cascadeRemovePersistentCollections($argument);
                }
            }
        }
        $result = call_user_func_array(array($this->client, $name), $arguments);
        return $result;
    }
}
