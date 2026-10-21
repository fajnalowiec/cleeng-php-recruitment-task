<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;

#[ApiResource(
    shortName: 'Offer',
    description: 'Purchasable offer',
    operations: [
        new GetCollection(
            routeName: 'app_offer_list',
            read: false,
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'List active offers'
                    ),
                ],
            ),
        ),
        new Get(
            routeName: 'app_offer_detail',
            read: false,
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'Return a single offer'
                    ),
                    '404' => new OpenApiResponse(
                        description: 'Offer not found',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'code' => ['type' => 'string', 'example' => 'offer_not_found'],
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
class OfferResource
{
    public ?int $id = null;
    public string $title = '';
    public int $price = 0;
    public string $currency = '';
    public string $billingPeriod = '';
}
