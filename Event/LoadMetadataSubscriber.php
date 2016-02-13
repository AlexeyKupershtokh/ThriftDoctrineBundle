<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events;

class LoadMetadataSubscriber implements EventSubscriber
{
    public function loadClassMetadata(\Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $className = $classMetadata->getName();
        $staticProperties = $classMetadata->getReflectionClass()->getStaticProperties();
        if (array_key_exists('_TSPEC', $staticProperties) && ($staticProperties['_TSPEC'] === null)) {
            new $className;
        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }
}
