<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\MockClock;

final class CheckoutApiTest extends WebTestCase
{
    public function testCheckout_CreatesActiveSubscription_whenRequestIsValid(): void
    {
        $client = self::createClient();

        $client->jsonRequest('POST', '/subscriptions', [
            'customerId' => $this->customerId($client, 'alice@example.com'),
            'offerId' => $this->offerId($client, 'Monthly Pass'),
        ]);

        self::assertResponseStatusCodeSame(201);
        $body = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('ACTIVE', $body['status']);
        self::assertNull($body['cancelledAt']);
        self::assertGreaterThan(
            new \DateTimeImmutable($body['startedAt']),
            new \DateTimeImmutable($body['expiresAt']),
        );
    }

    public function testBug001(): void
    {
        $client = self::createClient();
        static::getContainer()->set('clock', new MockClock(
            new \DateTimeImmutable('2026-08-31T10:00:00', new \DateTimeZone('UTC')),
        ));

        $client->jsonRequest('POST', '/subscriptions', [
            'customerId' => $this->customerId($client, 'alice@example.com'),
            'offerId' => $this->offerId($client, 'Monthly Pass'),
        ]);

        self::assertResponseStatusCodeSame(201);
        $body = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('2026-08-31T10:00:00+00:00', $body['startedAt']);
        self::assertSame('2026-09-30T10:00:00+00:00', $body['expiresAt']);
    }

    public function testCheckout_Returns404_whenCustomerUnknown(): void
    {
        $client = self::createClient();

        $client->jsonRequest('POST', '/subscriptions', [
            'customerId' => 999999,
            'offerId' => $this->offerId($client, 'Monthly Pass'),
        ]);

        self::assertResponseStatusCodeSame(404);
        self::assertSame('customer_not_found', $this->errorCode($client));
    }

    public function testCheckout_Returns404_whenOfferUnknown(): void
    {
        $client = self::createClient();

        $client->jsonRequest('POST', '/subscriptions', [
            'customerId' => $this->customerId($client, 'alice@example.com'),
            'offerId' => 999999,
        ]);

        self::assertResponseStatusCodeSame(404);
        self::assertSame('offer_not_found', $this->errorCode($client));
    }

    public function testCheckout_Returns404_whenOfferInactive(): void
    {
        $client = self::createClient();

        $client->jsonRequest('POST', '/subscriptions', [
            'customerId' => $this->customerId($client, 'alice@example.com'),
            'offerId' => $this->offerId($client, 'Legacy Plan'),
        ]);

        self::assertResponseStatusCodeSame(404);
        self::assertSame('offer_not_found', $this->errorCode($client));
    }

    public function testCheckout_Returns409_whenCustomerAlreadyActivelySubscribed(): void
    {
        $client = self::createClient();
        $payload = [
            'customerId' => $this->customerId($client, 'alice@example.com'),
            'offerId' => $this->offerId($client, 'Monthly Pass'),
        ];

        $client->jsonRequest('POST', '/subscriptions', $payload);
        $client->jsonRequest('POST', '/subscriptions', $payload);

        self::assertResponseStatusCodeSame(409);
        self::assertSame('already_subscribed', $this->errorCode($client));
    }

    public function testCheckout_Returns422WithViolations_whenBodyInvalid(): void
    {
        $client = self::createClient();

        $client->jsonRequest('POST', '/subscriptions', ['customerId' => -1]);

        self::assertResponseStatusCodeSame(422);
        $body = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('validation_failed', $body['error']['code']);
        $fields = array_column($body['error']['violations'], 'field');
        self::assertContains('customerId', $fields);
        self::assertContains('offerId', $fields);
    }

    private function customerId(object $client, string $email): int
    {
        $customer = static::getContainer()->get('doctrine')
            ->getRepository(\App\Entity\Customer::class)
            ->findOneBy(['email' => $email]);

        return $customer->getId();
    }

    private function offerId(object $client, string $title): int
    {
        $offer = static::getContainer()->get('doctrine')
            ->getRepository(\App\Entity\Offer::class)
            ->findOneBy(['title' => $title]);

        return $offer->getId();
    }

    private function errorCode(object $client): string
    {
        return json_decode($client->getResponse()->getContent(), true)['error']['code'];
    }
}
