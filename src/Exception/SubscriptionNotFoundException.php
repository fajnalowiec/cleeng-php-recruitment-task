<?php

declare(strict_types=1);

namespace App\Exception;

final class SubscriptionNotFoundException extends ApiException
{
    public static function withId(int $id): self
    {
        return new self(
            'subscription_not_found',
            sprintf('Subscription %d not found.', $id),
            404,
        );
    }
}
