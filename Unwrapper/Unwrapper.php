<?php

namespace AlexeyKupershtokh\ThriftDoctrineBundle\Unwrapper;


use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\VarDumper\VarDumper;

class Unwrapper
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function cascadeRemovePersistentCollections($document)
    {
        $visited = array();
        $this->doCascadeRemovePersistentCollections($document, $visited);
    }

    public function doCascadeRemovePersistentCollections($document, array &$visited)
    {
        $oid = spl_object_hash($document);
        if (isset($visited[$oid])) {
            return; // Prevent infinite recursion
        }

        $visited[$oid] = $document; // mark visited

        $class = $this->dm->getClassMetadata(get_class($document));

        $associationMappings = $class->associationMappings;

        foreach ($associationMappings as $assoc) {
            $relatedDocuments = $class->reflFields[$assoc['fieldName']]->getValue($document);
            if ($relatedDocuments instanceof PersistentCollection) {
                $relatedDocuments = $relatedDocuments->getValues();
            }
            if (is_array($relatedDocuments)) {
                $class->reflFields[$assoc['fieldName']]->setValue($document, $relatedDocuments);
                foreach ($relatedDocuments as $relatedDocument) {
                    $this->doCascadeRemovePersistentCollections($relatedDocument, $visited);
                }
            } elseif (is_object($relatedDocuments)) {
                $this->doCascadeRemovePersistentCollections($relatedDocuments, $visited);
            }
        }
    }
}
