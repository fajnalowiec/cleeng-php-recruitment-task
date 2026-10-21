<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\BillingPeriod;
use App\Entity\Customer;
use App\Entity\Offer;
use App\Repository\SubscriptionRepository;
use App\Service\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class CheckoutServiceTest extends TestCase
{
    public function testCheckout_SetsExpiryOnSameDayNextMonth_whenOfferIsMonthly(): void
    {
        $sut = $this->createSut('2026-01-15 12:00:00');

        $subscription = $sut->checkout(new Customer('a@example.com'), $this->offer(BillingPeriod::P1M));

        self::assertSame('2026-02-15', $subscription->getExpiresAt()->format('Y-m-d'));
    }

    public function testCheckout_SetsExpiryOnSameDayNextYear_whenOfferIsYearly(): void
    {
        $sut = $this->createSut('2026-03-15 12:00:00');

        $subscription = $sut->checkout(new Customer('a@example.com'), $this->offer(BillingPeriod::P1Y));

        self::assertSame('2027-03-15', $subscription->getExpiresAt()->format('Y-m-d'));
    }

    public function testCheckout_SetsExpiryOnLastDayOfTargetMonth_whenMonthlyStartsOnMonthEnd(): void
    {
        $sut = $this->createSut('2026-01-31 12:00:00');

        $subscription = $sut->checkout(new Customer('a@example.com'), $this->offer(BillingPeriod::P1M));

        self::assertSame('2026-02-28', $subscription->getExpiresAt()->format('Y-m-d'));
    }

    public function testCheckout_SetsExpiryOnLastDayOfTargetMonth_whenYearlyStartsOnLeapDay(): void
    {
        $sut = $this->createSut('2028-02-29 12:00:00');

        $subscription = $sut->checkout(new Customer('a@example.com'), $this->offer(BillingPeriod::P1Y));

        self::assertSame('2029-02-28', $subscription->getExpiresAt()->format('Y-m-d'));
    }

    public function testCheckout_SetsStartedAtToCurrentTime(): void
    {
        $sut = $this->createSut('2026-04-10 09:30:00');

        $subscription = $sut->checkout(new Customer('a@example.com'), $this->offer(BillingPeriod::P1M));

        self::assertSame('2026-04-10T09:30:00+00:00', $subscription->getStartedAt()->format(DATE_ATOM));
    }

    private function createSut(string $now): CheckoutService
    {
        $subscriptions = $this->createStub(SubscriptionRepository::class);
        $subscriptions->method('findActiveFor')->willReturn(null);

        return new CheckoutService(
            new MockClock(new \DateTimeImmutable($now, new \DateTimeZone('UTC'))),
            $subscriptions,
            $this->createStub(EntityManagerInterface::class),
        );
    }

    private function offer(BillingPeriod $period): Offer
    {
        return new Offer('Test Offer', 999, 'USD', $period);
    }
}
