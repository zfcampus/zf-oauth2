<?php
return array(
    'service_manager' => array(
        'invokables' => array(
            'hydrator_filter_exclude_user' => 'RollNApi\Hydrator\Filter\ExcludeUser',
        ),
    ),
    'data-fixture' => array(
        'RollNApi_fixture' => __DIR__ . '/../src/RollNApi/Fixture',
    ),
    'doctrine' => array(
        'driver' => array(
            'rollnapi_driver' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\XmlDriver',
                'paths' => array(
                    0 => __DIR__ . '/xml',
                ),
            ),
            'orm_default' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
                'drivers' => array(
                    'RollNApi\\Entity' => 'rollnapi_driver',
                ),
            ),
        ),
    ),
    'zf-apigility-doctrine-query-provider' => array(
        'invokables' => array(
            'default_orm' => 'RollNApi\Query\Provider\DefaultOrm',
            'user_album_default' => 'RollNApi\Query\Provider\UserAlbum\DefaultQueryProvider',
            'user_album_fetch_all' => 'RollNApi\Query\Provider\UserAlbum\FetchAllQueryProvider',
        )
    ),
    'zf-apigility-doctrine-query-create-filter' => array(
        'invokables' => array(
            'default' => 'RollNApi\Query\CreateFilter\DefaultCreateFilter',
            'user_album' => 'RollNApi\Query\CreateFilter\UserAlbumCreateFilter',
        )
    ),
    'router' => array(
        'routes' => array(
            'roll-n-api.rest.doctrine.artist' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/artist[/:artist_id]',
                    'defaults' => array(
                        'controller' => 'RollNApi\\V1\\Rest\\Artist\\Controller',
                    ),
                ),
            ),
            'roll-n-api.rest.doctrine.album' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/album[/:album_id]',
                    'defaults' => array(
                        'controller' => 'RollNApi\\V1\\Rest\\Album\\Controller',
                    ),
                ),
            ),
            'roll-n-api.rest.doctrine.user-album' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user-album[/:user_album_id]',
                    'defaults' => array(
                        'controller' => 'RollNApi\\V1\\Rest\\UserAlbum\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'roll-n-api.rest.doctrine.artist',
            1 => 'roll-n-api.rest.doctrine.album',
            2 => 'roll-n-api.rest.doctrine.user-album',
        ),
    ),
    'zf-rest' => array(
        'RollNApi\\V1\\Rest\\Artist\\Controller' => array(
            'listener' => 'RollNApi\\V1\\Rest\\Artist\\ArtistResource',
            'route_name' => 'roll-n-api.rest.doctrine.artist',
            'route_identifier_name' => 'artist_id',
            'entity_identifier_name' => 'id',
            'collection_name' => 'artist',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'RollNApi\\Entity\\Artist',
            'collection_class' => 'RollNApi\\V1\\Rest\\Artist\\ArtistCollection',
            'use_oauth2' => true,
        ),
        'RollNApi\\V1\\Rest\\Album\\Controller' => array(
            'listener' => 'RollNApi\\V1\\Rest\\Album\\AlbumResource',
            'route_name' => 'roll-n-api.rest.doctrine.album',
            'route_identifier_name' => 'album_id',
            'entity_identifier_name' => 'id',
            'collection_name' => 'album',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'RollNApi\\Entity\\Album',
            'collection_class' => 'RollNApi\\V1\\Rest\\Album\\AlbumCollection',
        ),
        'RollNApi\\V1\\Rest\\UserAlbum\\Controller' => array(
            'listener' => 'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumResource',
            'route_name' => 'roll-n-api.rest.doctrine.user-album',
            'route_identifier_name' => 'user_album_id',
            'entity_identifier_name' => 'id',
            'collection_name' => 'user_album',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'RollNApi\\Entity\\UserAlbum',
            'collection_class' => 'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumCollection',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'RollNApi\\V1\\Rest\\Artist\\Controller' => 'HalJson',
            'RollNApi\\V1\\Rest\\Album\\Controller' => 'HalJson',
            'RollNApi\\V1\\Rest\\UserAlbum\\Controller' => 'HalJson',
        ),
        'accept-whitelist' => array(
            'RollNApi\\V1\\Rest\\Artist\\Controller' => array(
                0 => 'application/vnd.roll-n-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'RollNApi\\V1\\Rest\\Album\\Controller' => array(
                0 => 'application/vnd.roll-n-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'RollNApi\\V1\\Rest\\UserAlbum\\Controller' => array(
                0 => 'application/vnd.roll-n-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content-type-whitelist' => array(
            'RollNApi\\V1\\Rest\\Artist\\Controller' => array(
                0 => 'application/vnd.roll-n-api.v1+json',
                1 => 'application/json',
            ),
            'RollNApi\\V1\\Rest\\Album\\Controller' => array(
                0 => 'application/vnd.roll-n-api.v1+json',
                1 => 'application/json',
            ),
            'RollNApi\\V1\\Rest\\UserAlbum\\Controller' => array(
                0 => 'application/vnd.roll-n-api.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'RollNApi\\Entity\\Artist' => array(
                'route_identifier_name' => 'artist_id',
                'entity_identifier_name' => 'id',
                'route_name' => 'roll-n-api.rest.doctrine.artist',
                'hydrator' => 'RollNApi\\V1\\Rest\\Artist\\ArtistHydrator',
            ),
            'RollNApi\\V1\\Rest\\Artist\\ArtistCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'roll-n-api.rest.doctrine.artist',
                'is_collection' => true,
            ),
            'RollNApi\\Entity\\Album' => array(
                'route_identifier_name' => 'album_id',
                'entity_identifier_name' => 'id',
                'route_name' => 'roll-n-api.rest.doctrine.album',
                'hydrator' => 'RollNApi\\V1\\Rest\\Album\\AlbumHydrator',
            ),
            'RollNApi\\V1\\Rest\\Album\\AlbumCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'roll-n-api.rest.doctrine.album',
                'is_collection' => true,
            ),
            'RollNApi\\Entity\\UserAlbum' => array(
                'route_identifier_name' => 'user_album_id',
                'entity_identifier_name' => 'id',
                'route_name' => 'roll-n-api.rest.doctrine.user-album',
                'hydrator' => 'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumHydrator',
            ),
            'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'roll-n-api.rest.doctrine.user-album',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-apigility' => array(
        'doctrine-connected' => array(
            'RollNApi\\V1\\Rest\\Artist\\ArtistResource' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'hydrator' => 'RollNApi\\V1\\Rest\\Artist\\ArtistHydrator',
            ),
            'RollNApi\\V1\\Rest\\Album\\AlbumResource' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'hydrator' => 'RollNApi\\V1\\Rest\\Album\\AlbumHydrator',
            ),
            'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumResource' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'hydrator' => 'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumHydrator',
                'query_providers' => array(
                    'default' => 'user_album_default',
                    'fetch_all' => 'user_album_fetch_all',
                ),
                'query_create_filter' => 'user_album',
            ),
        ),
    ),
    'doctrine-hydrator' => array(
        'RollNApi\\V1\\Rest\\Artist\\ArtistHydrator' => array(
            'entity_class' => 'RollNApi\\Entity\\Artist',
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => true,
            'strategies' => array(),
            'use_generated_hydrator' => true,
        ),
        'RollNApi\\V1\\Rest\\Album\\AlbumHydrator' => array(
            'entity_class' => 'RollNApi\\Entity\\Album',
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => true,
            'strategies' => array(),
            'use_generated_hydrator' => true,
        ),
        'RollNApi\\V1\\Rest\\UserAlbum\\UserAlbumHydrator' => array(
            'entity_class' => 'RollNApi\\Entity\\UserAlbum',
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => true,
            'strategies' => array(),
            'use_generated_hydrator' => true,
            'filters' => array(
                'exclude_user' => array(
                    'condition' => 'and', //optional, default: FilterComposite::CONDITION_OR,
                    'filter' => 'hydrator_filter_exclude_user', // a name in the Service Manager
                ),
            ),
        ),
    ),
);
