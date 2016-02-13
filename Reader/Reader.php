<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Reader;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Thrift\Base\TBase;
use Thrift\Type\TType;

class Reader
{
    /**
     * Read thrift spec from the class
     *
     * @param $className
     * @return mixed
     * @throws \Exception
     */
    public function loadSpec($className)
    {
        if (is_subclass_of($className, '\Thrift\Base\TBase')) {
            $inst = new $className;
            if (isset($className::$_TSPEC)) {
                return $className::$_TSPEC;
            }
        }
        throw new \Exception('Couldn\'t load specification of the ' . $className . ' class');
    }

    /**
     * Convert thrift class spec to doctrine ClassMetadataInfo
     *
     * @param $className
     * @return ClassMetadataInfo
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Exception
     */
    public function read($className)
    {
        $cmi = new ClassMetadataInfo($className);
        $types = array(
            TType::BOOL => 'bool',
            TType::I32 => 'integer',
            TType::I64 => 'integer',
            TType::DOUBLE => 'float',
            TType::UTF7 => 'string',
            TType::UTF8 => 'string',
            TType::LST => 'many',
            TType::STRUCT => 'one',
            TType::MAP => 'hash',
        );
        $spec = $this->loadSpec($className);
        foreach ($spec as $id => $fieldSpec) {
            if (!isset($types[$fieldSpec['type']])) {
                throw new \Exception('Unknown thrift -> doctrine type conversion');
            }
            $type = $types[$fieldSpec['type']];
            if ($fieldSpec['type'] == TType::LST) {
                // list
                if ($fieldSpec['etype'] == TType::STRUCT) {
                    // list of structs
                    $mapping = array(
                        'fieldName' => $fieldSpec['var'],
                        'name' => $id . '_' . $fieldSpec['var'],
                        'embedded' => true,
                        'type' => $type,
                        'targetDocument' => substr($fieldSpec['elem']['class'], 1),
                    );
                } else {
                    // list of simple types
                    $mapping = array(
                        'fieldName' => $fieldSpec['var'],
                        'name' => $id . '_' . $fieldSpec['var'],
                        'type' => 'hash',
                    );
                }
            } elseif ($fieldSpec['type'] == TType::STRUCT) {
                // single struct
                $mapping = array(
                    'fieldName' => $fieldSpec['var'],
                    'name' => $id . '_' . $fieldSpec['var'],
                    'embedded' => true,
                    'type' => $type,
                    'targetDocument' => substr($fieldSpec['class'], 1),
                );
            } else {
                // simple field
                $mapping = array(
                    'fieldName' => $fieldSpec['var'],
                    'name' => $id . '_' . $fieldSpec['var'],
                    'type' => $type,
                );
            }
            $cmi->mapField($mapping);
        }
        return $cmi;
    }
}
