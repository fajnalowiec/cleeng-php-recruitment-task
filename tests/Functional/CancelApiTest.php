<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Subscription;
use App\Entity\SubscriptionStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\MockClock;

final class CancelApiTest extends WebTestCase
{
    public function testCancelActiveSubscription_ReturnsCancelledAndKeepsExpiry(): void
    {
        $client = self::createClient();

        $subscription = $this->findActiveSubscription();
        $id = $subscription->getId();
        $customerId = $subscription->getCustomer()->getId();
        $offerId = $subscription->getOffer()->getId();
        $startedAt = $subscription->getStartedAt()->format(DATE_ATOM);
        $expiresAt = $subscription->getExpiresAt()->format(DATE_ATOM);

        static::getContainer()->set('clock', new MockClock(
            new \DateTimeImmutable('2026-01-15T12:00:00+00:00'),
        ));

        $client->jsonRequest('POST', '/subscriptions/'.$id.'/cancel');

        self::assertResponseStatusCodeSame(200);

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame($id, $body['id']);
        self::assertSame($customerId, $body['customerId']);
        self::assertSame($offerId, $body['offerId']);
        self::assertSame('CANCELLED', $body['status']);
        self::assertSame($startedAt, $body['startedAt']);
        self::assertSame($expiresAt, $body['expiresAt']);
        self::assertSame('2026-01-15T12:00:00+00:00', $body['cancelledAt']);
    }

    private function findActiveSubscription(): Subscription
    {
        $subscription = static::getContainer()->get('doctrine')
            ->getRepository(Subscription::class)
            ->findOneBy(['status' => SubscriptionStatus::ACTIVE]);

        self::assertNotNull($subscription);

        return $subscription;
    }
}
