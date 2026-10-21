<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private Customer $customer,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private Offer $offer,
        #[ORM\Column]
        private \DateTimeImmutable $startedAt,
        #[ORM\Column]
        private \DateTimeImmutable $expiresAt,
        #[ORM\Column(length: 20, enumType: SubscriptionStatus::class)]
        private SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $cancelledAt = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionStatus $status): void
    {
        $this->status = $status;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): void
    {
        $this->cancelledAt = $cancelledAt;
    }
}
