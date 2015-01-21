<?php

namespace RollNApi\Query\Provider\UserAlbum;

use ZF\Apigility\Doctrine\Server\Query\Provider\DefaultOrm;
use ZF\Rest\ResourceEvent;

class DefaultQueryProvider extends DefaultOrm
{
    public function createQuery(ResourceEvent $event, $entityClass, $parameters)
    {
        $validate = $this->validateOAuth2(null);
        if ($validate instanceof ApiProblem) {
            return $validate;
        }

        $identity = $event->getIdentity()->getAuthenticationIdentity();
        $userId = $identity['user_id'];

        $user = $this->getObjectManager()->getRepository('RollNApi\Entity\User')->find($userId);

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('row')
            ->from($entityClass, 'row')
            ->andwhere('row.user = :user')
            ->setParameter('user', $user)
            ;

        return $queryBuilder;
    }
}
