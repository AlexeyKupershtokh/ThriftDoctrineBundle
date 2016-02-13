# ThriftDoctrineBundle
A useful toolkit for persisting generated Thrift models via Doctrine ODM.

```yaml
document_managers:
    default:
        mappings:
            MyProject\Document:
                type: annotation
                prefix: MyProject\Document
                dir: %kernel.root_dir%/../src/MyProject/Document
                is_bundle: false
            ThriftBundle:
                type: yml
                dir: Resources/config/doctrine/mapping
                prefix: ThriftNamespace
        auto_mapping: true
        metadata_cache_driver: apc
services:
    doctrine.unwrapper_extension:
        class: AlexeyKupershtokh\ThriftDoctrineBundle\Twig\UnwrapperExtension
        arguments:
            - @doctrine.odm.mongodb.document_manager
        public: false
        tags:
            - { name: twig.extension }
parameters:
    doctrine_mongodb.odm.cache.apc.class: AlexeyKupershtokh\ThriftDoctrineBundle\Cache\InstantiatingApcCache

```

Requires a controller alternative to Overblog\ThriftBundle's one in order to wrap handlers to make them avoid sending PersistentCollection:
```php
class ThriftController extends Controller
{
    /**
     * HTTP Entry point
     */
    public function serverAction()
    {
        if (!($extensionName = $this->getRequest()->get('extensionName'))) {
            throw $this->createNotFoundException('Unable to get config name');
        }

        $servers = $this->container->getParameter('thrift.config.servers');

        if (!isset($servers[$extensionName])) {
            throw $this->createNotFoundException(sprintf('Unknown config "%s"', $extensionName));
        }

        $server = $servers[$extensionName];

        $handler = $this->container->get($server['handler']);
        $wrappedHandler = new HandlerWrapper($handler);
        $wrappedHandler->setDocumentManager($this->container->get('doctrine_mongodb.odm.document_manager'));

        $processor = $this->container->get('thrift.factory')->getProcessorInstance($server['service'], $wrappedHandler);

        $server = new HttpServer($processor, $server['service_config']);

        $server->getHeader();

        $server->run();

        $headers = array('Content-Type' => 'application/x-thrift');
        return new \Symfony\Component\HttpFoundation\Response(null, 200, $headers);
        //$response = new StreamedResponse(function() use ($server) { $server->run(); });
        //return $response;
    }
}
```