<?php

namespace RollNApi\Query\Provider;

use ZF\Apigility\Doctrine\Server\Query\Provider\DefaultOrm as ZFDefaultOrm;
use ZF\Rest\ResourceEvent;
use OAuth2\Server as OAuth2Server;
use ZF\ApiProblem\ApiProblem;

class DefaultOrm extends ZFDefaultOrm
{
    public function createQuery(ResourceEvent $event, $entityClass, $parameters)
    {
        $validate = $this->validateOAuth2();
        if ($validate instanceof ApiProblem) {
            return $validate;
        }

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('row')
            ->from($entityClass, 'row')
            ;

        return $queryBuilder;
    }
}
