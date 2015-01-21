<?php

namespace RollNApi\Query\CreateFilter;

use ZF\Apigility\Doctrine\Server\Query\CreateFilter\DefaultCreateFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\ResourceEvent;

class UserAlbumCreateFilter extends DefaultCreateFilter
{
    /**
     * @param string $entityClass
     * @param array  $data
     *
     * @return array
     */
    public function filter(ResourceEvent $event, $entityClass, $data)
    {
        $validate = $this->validateOAuth2();
        if ($validate instanceof ApiProblem) {
            return $validate;
        }

        $request = $this->getServiceLocator()->getServiceLocator()->get('Request')->getQuery()->toArray();
        $identity = $event->getIdentity()->getAuthenticationIdentity();
        $data->user = $identity['user_id'];

        return $data;
    }
}
