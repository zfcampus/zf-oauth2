OAuth2 Server for Doctrine
==========================

Requirements
------------

Several modules are required through [composer](http://getcomposer.org).  Depending on your use of ORM or ODM you should include ```doctrine/doctrine-orm-module``` and/or ```doctrine/doctrine-mongo-odm-module``` and their supporting libraries.

Suggested Improvements
----------------------

```
zfcampus/zf-apigility-doctrine
zfcampus/zf-doctrine-querybuilder
```


Entity Relationship Diagram
---------------------------

In order to understand how OAuth2 works you should understand the ERD.  This is stored in a [Skipper](http://www.skipper18.com) file.  If you do not have Skipper and you are writing a Doctrine application now would be a good time to consider an upgrade to your practices.

The ERD is here: https://github.com/zfcampus/zf-oauth2/blob/master/media/OAuth2.skipper
If you don't have Skipper yet you can see a PDF here: https://github.com/zfcampus/zf-oauth2/blob/master/media/OAuth2.pdf

Because you'll be integrating zf-oauth2 Doctrine with your own ERD you may include the externally stored https://github.com/zfcampus/zf-oauth2/blob/master/media/ZF%20OAuth2.skipper bundle into your ERD.


Configuration
-------------

Copy ```config/oauth2.doctrine.global.php.dist``` to your autoload directory and rename to ```oauth2.doctrine.global.php```  If you are using the default doctrine.entitymanager.orm_default you will still need to change this file.

The OAuth2 Doctrine expects a User entity jointed to ZF\OAuth2\Entity\Client.  Because your ERD includes but does not associate your custom User entity to ZF\OAuth2\Entity\Client an association is made dynamically.  This is configured in the ```oauth2.doctrine.global.php``` file:

```
'zf-oauth2' => array(
    'storage' => 'ZF\OAuth2\Adapter\DoctrineAdapter',
    'storage_settings' => array(
        ...
        // Dynamically map the user_entity to the client_entity
        'dynamic_mapping' => array(
            'user_entity' => array(
                'entity' => 'RollNApi\Entity\User',
                'field' => 'user',
            ),
            'client_entity' => array(
                'entity' => 'ZF\OAuth2\Entity\Client',
                'field' => 'client',
            ),
        ),
        ...
```

Change the dynamic_mapping to your custom User entity and be sure it includes $client, addClient, and removeClient.

Next change the
```
        'mapping' => array(
            'ZF\OAuth2\Mapper\User' => array(
```
section and define your user there.  Only id, username, and password are required.  The mappings are customizable for example you may map the username to an email if you choose.


Using Default Entities
----------------------

Details for creating your database with the included entities are outside the scope of this project.  Generally this is done through doctrine-orm-module with ```php public/index.php orm:schema-tool:create```

By default this module uses the entities provided but you may toggle this and use your own entites (and map them in the mapping config section) by toggling this flag:

```
'zf-oauth2' => array(
    'storage_settings' => array(
        'enable_default_entities' => true,
        ...
```


Securing Resources with zf-apigility-doctrine
------------------------------------------

This module is supported directly by zf-apigility-doctrine.  To add security to your resources create a DefaultOrm Query Provider and include:

```
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

```

See (zfcampus/zf-apigility-doctrine)[https://github.com/zfcampus/zf-apigility-doctrine] for more details on Query Providers.

