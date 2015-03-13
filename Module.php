<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Zend\ModuleManager\ModuleManager;
use ZF\OAuth2\EventListener\DynamicMappingSubscriber;

/**
 * ZF2 module
 */
class Module
{
    public function init(ModuleManager $moduleManager)
    {
    }

    public function onBootstrap($e)
    {
        $app     = $e->getParam('application');
        $sm      = $app->getServiceManager();
        $config = $sm->get('Config');

        // Add the default entity driver only if specified in configuration
        if (isset($config['zf-oauth2']['storage_settings']['enable_default_entities'])
            && $config['zf-oauth2']['storage_settings']['enable_default_entities']) {
            $chain = $sm->get($config['zf-oauth2']['storage_settings']['driver']);
            $chain->addDriver(new XmlDriver(__DIR__ . '/config/xml'), 'ZF\OAuth2\Entity');
        }

        if (isset($config['zf-oauth2']['storage_settings']['dynamic_mapping'])
            && $config['zf-oauth2']['storage_settings']['dynamic_mapping']) {

            $dynamicMappingSubscriber = new DynamicMappingSubscriber($config['zf-oauth2']['storage_settings']['dynamic_mapping']);

            $eventManager = $sm->get($config['zf-oauth2']['storage_settings']['event_manager']);

            // $em is an instance of the Event Manager
            $eventManager->addEventSubscriber($dynamicMappingSubscriber);
        }
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
