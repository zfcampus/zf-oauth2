<?php

namespace RollNApi;

use ZF\Apigility\Provider\ApigilityProviderInterface;
use Zend\Console\Console;
use Zend\Mvc\MvcEvent;
use OAuth2\Request as OAuth2Request;
use OAuth2\Storage\Memory as OAuth2Memory;
use OAuth2\GrantType\JwtBearer as OAuth2JwtBearer;
use Exception;

class Module implements ApigilityProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        // Store JWT grant types
        $server = $e->getApplication()->getServiceManager()->get('ZF\OAuth2\Service\OAuth2Server');
        $objectManager = $e->getApplication()->getServiceManager()->get('doctrine.entitymanager.orm_default');

        try {
            $jwt = $objectManager->getRepository('ZF\OAuth2\Entity\Jwt')->findAll();

            $clientKeys = array();
            foreach ($jwt as $row) {
                $clientKeys[$row->getClient()->getClientId()] = array(
                    'subject' => $row->getSubject(),
                    'key' => $row->getPublicKey(),
                );
            }

            $storage = new OAuth2Memory(array('jwt' => $clientKeys));
            $audience = 'http://localhost:8083';
            $grantType = new OAuth2JwtBearer($storage, $audience);

            if ($clientKeys) {
                $server->addGrantType($grantType);
            }
        } catch (Exception $e) {
            // Allow failure to install db
        }

#        $eventManager = $e->getApplication()->getEventManager();
#        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'));
    }

    public function onDispatch(MvcEvent $e)
    {
        if (!Console::isConsole()) {
            $server = $e->getApplication()->getServiceManager()->get('ZF\OAuth2\Service\OAuth2Server');

            if (!$server->verifyResourceRequest(OAuth2Request::createFromGlobals())) {
                throw new \Exception('Not Authorized');
            }
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
