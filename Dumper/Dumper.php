<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Dumper;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Symfony\Component\Yaml\Yaml;

class Dumper
{
    /**
     * @param ClassMetadataInfo $cmi
     * @return string
     */
    public function dump(ClassMetadataInfo $cmi)
    {
        $class = array();
        $class['type'] = 'embeddedDocument';
        $class['fields'] = $cmi->fieldMappings;
        $content = array($cmi->name => $class);
        return Yaml::dump($content, 10);
    }
}
