<?php

/**
 * This doctrine event subscriber will join a user table to the client table
 * thereby freeing the user table from the OAuth2 contraints
 */

namespace ZF\OAuth2\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class UserClientSubscriber implements EventSubscriber
{
    protected $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        // the $metadata is the whole mapping info for this class
        $metadata = $eventArgs->getClassMetadata();

        if ($metadata->getName() == $this->config['user_entity']['entity']) {
            $metadata->mapOneToMany(array(
                'targetEntity' => $this->config['client_entity']['entity'],
                'fieldName' => $this->config['client_entity']['field'],
                'mappedBy' => $this->config['user_entity']['field'],
            ));
        } elseif ($metadata->getName() == $this->config['client_entity']['entity']) {
            $metadata->mapManyToOne(array(
                'targetEntity' => $this->config['user_entity']['entity'],
                'fieldName' => $this->config['user_entity']['field'],
            ));
        }
    }
}
