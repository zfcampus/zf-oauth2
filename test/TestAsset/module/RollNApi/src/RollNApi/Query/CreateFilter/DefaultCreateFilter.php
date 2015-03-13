<?php

namespace RollNApi\Query\CreateFilter;

use ZF\Apigility\Doctrine\Server\Query\CreateFilter\DefaultCreateFilter as ZFDefaultCreateFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\ResourceEvent;

class DefaultCreateFilter extends ZFDefaultCreateFilter
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

        return $data;
    }
}
