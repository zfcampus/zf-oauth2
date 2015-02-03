<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2;

use Doctrine\ORM\Mapping\Driver\XmlDriver as OrmXmlDriver;
use Doctrine\Odm\Mapping\Driver\XmlDriver as OdmXmlDriver;
use Zend\ModuleManager\ModuleManager;
use ZF\OAuth2\EventListener\UserClientSubscriber;

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

        // Add the default ORM entity driver only if specified in configuration
        if (isset($config['zf-oauth2']['storage_settings']['enable_default_entities'])
            && $config['zf-oauth2']['storage_settings']['enable_default_entities']) {
            $chain = $sm->get($config['zf-oauth2']['storage_settings']['driver']);
            $chain->addDriver(new OrmXmlDriver(__DIR__ . '/config/orm'), 'ZF\OAuth2\Entity');
        }

        // Add the default ODM document driver if specified
        if (isset($config['zf-oauth2']['storage_settings']['enable_default_documents'])
            && $config['zf-oauth2']['storage_settings']['enable_default_documents']) {
            $chain = $sm->get($config['zf-oauth2']['storage_settings']['driver']);
            $chain->addDriver(new OdmXmlDriver(__DIR__ . '/config/odm'), 'ZF\OAuth2\Document');
        }

        if (isset($config['zf-oauth2']['storage_settings']['dynamic_mapping'])
            && $config['zf-oauth2']['storage_settings']['dynamic_mapping']) {

            $userClientSubscriber = new UserClientSubscriber($config['zf-oauth2']['storage_settings']['dynamic_mapping']);

            $eventManager = $sm->get($config['zf-oauth2']['storage_settings']['event_manager']);

            // $em is an instance of the Event Manager
            $eventManager->addEventSubscriber($userClientSubscriber);
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
