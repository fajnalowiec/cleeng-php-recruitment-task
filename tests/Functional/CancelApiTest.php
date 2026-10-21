<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Subscription;
use App\Entity\SubscriptionStatus;
use App\Exception\CannotCancelExpiredException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\MockClock;

final class CancelApiTest extends WebTestCase
{
    public function testCancelActiveSubscription_ReturnsCancelledAndKeepsExpiry(): void
    {
        $client = self::createClient();

        $subscription = $this->findSubscription(SubscriptionStatus::ACTIVE);
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

    public function testCancelAlreadyCancelledSubscription_ReturnsUnchangedRepresentation(): void
    {
        $client = self::createClient();

        $subscription = $this->findSubscription(SubscriptionStatus::CANCELLED);
        $id = $subscription->getId();
        $customerId = $subscription->getCustomer()->getId();
        $offerId = $subscription->getOffer()->getId();
        $startedAt = $subscription->getStartedAt()->format(DATE_ATOM);
        $expiresAt = $subscription->getExpiresAt()->format(DATE_ATOM);
        $cancelledAt = $subscription->getCancelledAt()->format(DATE_ATOM);

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
        self::assertSame($cancelledAt, $body['cancelledAt']);
    }

    public function testCancelExpiredSubscription_Returns409WithErrorCode(): void
    {
        $client = self::createClient();

        $subscription = $this->findSubscription(SubscriptionStatus::EXPIRED);
        $expected = CannotCancelExpiredException::for($subscription->getId());

        $client->jsonRequest('POST', '/subscriptions/'.$subscription->getId().'/cancel');

        self::assertResponseStatusCodeSame($expected->getHttpStatus());
        self::assertSame($expected->getErrorCode(), $this->errorCode($client));
        self::assertSame($expected->getMessage(), $this->errorMessage($client));
    }

    private function findSubscription(SubscriptionStatus $status): Subscription
    {
        $subscription = static::getContainer()->get('doctrine')
            ->getRepository(Subscription::class)
            ->findOneBy(['status' => $status]);

        self::assertNotNull($subscription);

        return $subscription;
    }

    private function errorCode(object $client): string
    {
        return $this->error($client)['code'];
    }

    private function errorMessage(object $client): string
    {
        return $this->error($client)['message'];
    }

    private function error(object $client): array
    {
        return json_decode($client->getResponse()->getContent(), true)['error'];
    }
}
