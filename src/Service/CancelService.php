<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Subscription;
use App\Entity\SubscriptionStatus;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;

final class CancelService
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly SubscriptionRepository $subscriptions,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function cancel(int $subscriptionId): Subscription
    {
        $subscription = $this->subscriptions->find($subscriptionId);

        $subscription->setStatus(SubscriptionStatus::CANCELLED);
        $subscription->setCancelledAt($this->clock->now());

        $this->entityManager->flush();

        return $subscription;
    }
}
