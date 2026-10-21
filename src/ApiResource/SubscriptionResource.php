<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use ApiPlatform\OpenApi\Model\RequestBody as OpenApiRequestBody;

#[ApiResource(
    shortName: 'Subscription',
    description: 'Customer subscription operations',
    operations: [
        new Post(
            routeName: 'app_subscription_checkout',
            write: false,
            deserialize: false,
            openapi: new OpenApiOperation(
                requestBody: new OpenApiRequestBody(
                    description: 'Checkout payload',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['customerId', 'offerId'],
                                'properties' => [
                                    'customerId' => [
                                        'type' => 'integer',
                                        'minimum' => 1,
                                    ],
                                    'offerId' => [
                                        'type' => 'integer',
                                        'minimum' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new OpenApiResponse(
                        description: 'Subscription created'
                    ),
                    '404' => new OpenApiResponse(
                        description: 'Customer or offer not found',
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
                    '409' => new OpenApiResponse(
                        description: 'Already subscribed',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'code' => ['type' => 'string', 'example' => 'already_subscribed'],
                                                'message' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new OpenApiResponse(
                        description: 'Validation failed',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'code' => ['type' => 'string', 'example' => 'validation_failed'],
                                                'message' => ['type' => 'string'],
                                            ],
                                        ],
                                        'violations' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'field' => ['type' => 'string'],
                                                    'message' => ['type' => 'string'],
                                                ],
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
        new Post(
            routeName: 'app_subscription_cancel',
            write: false,
            deserialize: false,
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'Subscription cancelled'
                    ),
                    '404' => new OpenApiResponse(
                        description: 'Subscription not found',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'code' => ['type' => 'string', 'example' => 'subscription_not_found'],
                                                'message' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '409' => new OpenApiResponse(
                        description: 'Cannot cancel expired subscription',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'code' => ['type' => 'string', 'example' => 'cannot_cancel_expired'],
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
class SubscriptionResource
{
    public ?int $id = null;
    public ?int $customerId = null;
    public ?int $offerId = null;
    public string $status = '';
    public string $startedAt = '';
    public string $expiresAt = '';
    public ?string $cancelledAt = null;
}
