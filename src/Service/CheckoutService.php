<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Offer;
use App\Entity\Subscription;
use App\Exception\AlreadySubscribedException;
use App\Exception\OfferNotFoundException;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use App\Entity\BillingPeriod;

final class CheckoutService
{

    private $castExpirationDateTable = [
            BillingPeriod::P1M->value => 'first day of next month',
            BillingPeriod::P1Y->value => 'first day of next year',
        ];

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly SubscriptionRepository $subscriptions,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function checkout(Customer $customer, Offer $offer): Subscription
    {
        if (!$offer->isActive()) {
            throw OfferNotFoundException::withId((int) $offer->getId());
        }

        if ($this->subscriptions->findActiveFor($customer, $offer) !== null) {
            throw AlreadySubscribedException::for((int) $customer->getId(), (int) $offer->getId());
        }

        $startedAt = $this->clock->now();
        $expiresAt = $this->calculateExpirationDate($startedAt, $offer->getBillingPeriod()->value);

        $subscription = new Subscription($customer, $offer, $startedAt, $expiresAt);
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $subscription;
    }

    private function calculateExpirationDate(\DateTimeImmutable $startedAt, string $period): \DateTimeImmutable
    {
        $currentDay = (int) $startedAt->format('j');
        $expiredAt = $startedAt->modify($this->castPeriodToFirstDayString($period));
        $maxDayOfNextMonth = (int) $expiredAt->format('t');
        $finalDay = min($currentDay, $maxDayOfNextMonth);
        return $expiredAt->setDate((int)$expiredAt->format('Y'), (int)$expiredAt->format('m'), $finalDay);
    }

    private function castPeriodToFirstDayString(string $period): string
    {
        if (!array_key_exists($period, $this->castExpirationDateTable)) {
            //TODO: handle the exception for API purpose in general ExceptionHandler if needed
            return $this->castExpirationDateTable[BillingPeriod::P1M];
        }
        return $this->castExpirationDateTable[$period];
    }
}
