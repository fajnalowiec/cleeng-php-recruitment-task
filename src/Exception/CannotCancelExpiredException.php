<?php

declare(strict_types=1);

namespace App\Exception;

final class CannotCancelExpiredException extends ApiException
{
    public static function for(int $subscriptionId): self
    {
        return new self(
            'cannot_cancel_expired',
            sprintf('Subscription %d is expired and cannot be cancelled.', $subscriptionId),
            409,
        );
    }
}
