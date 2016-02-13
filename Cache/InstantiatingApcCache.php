<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Cache;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

class InstantiatingApcCache extends ApcCache
{
    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        /** @var ClassMetadata $result */
        $classMetadata = parent::doFetch($id);
        if ($classMetadata instanceof ClassMetadata) {
            $className = $classMetadata->getName();
            $staticProperties = $classMetadata->getReflectionClass()->getStaticProperties();
            if (array_key_exists('_TSPEC', $staticProperties) && ($staticProperties['_TSPEC'] === null)) {
                new $className;
            }
        }
        return $classMetadata;
    }
}
