<?php

namespace ZF\OAuth2\Factory;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractDoctrineResourceFactory
 *
 * @package ZF\Apigility\Doctrine\Server\Resource
 */
class DoctrineMapperFactory implements AbstractFactoryInterface
{

    /**
     * Cache of canCreateServiceWithName lookups
     * @var array
     */
    protected $lookupCache = array();

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return bool
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (array_key_exists($requestedName, $this->lookupCache)) {
            return $this->lookupCache[$requestedName];
        }

        if (!$serviceLocator->has('Config')) {
            // @codeCoverageIgnoreStart
            return false;
        }
            // @codeCoverageIgnoreEnd

        // Validate object is set
        $config = $serviceLocator->get('Config');

        if (!isset($config['zf-oauth2']['storage_settings']['mapping'])
            || !isset($config['zf-oauth2']['storage_settings']['mapping'][$requestedName])
        ) {
            $this->lookupCache[$requestedName] = false;

            return false;
        }

        // Validate if class a valid Mapper
        $className = $requestedName;
        $className = $this->normalizeClassname($className);

        $reflection = new \ReflectionClass($className);
        if (!$reflection->isSubclassOf('\ZF\OAuth2\Mapper\AbstractMapper')) {
            // @codeCoverageIgnoreStart
            throw new ServiceNotFoundException(
                sprintf(
                    '%s requires that a valid class is specified for factory %s; no service found',
                    __METHOD__,
                    $requestedName
                )
            );
        }
        // @codeCoverageIgnoreEnd

        // Validate object manager
        $config = $config['zf-oauth2']['storage_settings']['object_manager'];
        if (!isset($config)) {
            // @codeCoverageIgnoreStart
            throw new ServiceNotFoundException(
                sprintf(
                    '%s requires that a valid "object_manager" is specified for listener %s; no service found',
                    __METHOD__,
                    $requestedName
                )
            );
        }
            // @codeCoverageIgnoreEnd

        $this->lookupCache[$requestedName] = true;

        return true;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return DoctrineResource
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $serviceLocator->get('Config');
        $mappingConfig = $config['zf-oauth2']['storage_settings']['mapping'][$requestedName];

        $className = $requestedName;
        $className = $this->normalizeClassname($className);

        $objectManager = $this->loadObjectManager($serviceLocator, $config);

        $mapper = new $className();
        $mapper->setObjectManager($objectManager);
        $mapper->setConfig($mappingConfig);
        $mapper->setApplicationConfig($config);

        return $mapper;
    }

    /**
     * @param $className
     *
     * @return string
     */
    protected function normalizeClassname($className)
    {
        return '\\' . ltrim($className, '\\');
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $config
     *
     * @return ObjectManager
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function loadObjectManager(ServiceLocatorInterface $serviceLocator, $config)
    {
        if ($serviceLocator->has($config['zf-oauth2']['storage_settings']['object_manager'])) {
            $objectManager = $serviceLocator->get($config['zf-oauth2']['storage_settings']['object_manager']);
        } else {
            // @codeCoverageIgnoreStart
            throw new ServiceNotCreatedException('The object_manager could not be found.');
        }
        // @codeCoverageIgnoreEnd
        return $objectManager;
    }
}
