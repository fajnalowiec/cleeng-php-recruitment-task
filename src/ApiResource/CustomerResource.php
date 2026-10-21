<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;

#[ApiResource(
    shortName: 'CustomerSubscriptions',
    description: 'Customer subscriptions listing',
    operations: [
        new GetCollection(
            routeName: 'app_customer_subscriptions',
            read: false,
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'List all customer subscriptions'
                    ),
                    '404' => new OpenApiResponse(
                        description: 'Customer not found',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'code' => ['type' => 'string', 'example' => 'customer_not_found'],
                                                'message' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
)]
class CustomerResource
{
    public ?int $id = null;
}
