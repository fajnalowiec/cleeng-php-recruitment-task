<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\CustomerNotFoundException;
use App\Exception\OfferNotFoundException;
use App\Http\CheckoutRequest;
use App\Http\SubscriptionJson;
use App\Repository\CustomerRepository;
use App\Repository\OfferRepository;
use App\Service\CancelService;
use App\Service\CheckoutService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class SubscriptionController
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly OfferRepository $offers,
        private readonly CheckoutService $checkoutService,
        private readonly CancelService $cancelService,
    ) {
    }

    #[Route('/subscriptions', methods: ['POST'])]
    public function checkout(#[MapRequestPayload] CheckoutRequest $request): JsonResponse
    {
        $customer = $this->customers->find($request->customerId)
            ?? throw CustomerNotFoundException::withId((int) $request->customerId);
        $offer = $this->offers->find($request->offerId)
            ?? throw OfferNotFoundException::withId((int) $request->offerId);

        $subscription = $this->checkoutService->checkout($customer, $offer);

        return new JsonResponse(SubscriptionJson::from($subscription), 201);
    }

    #[Route('/subscriptions/{id}/cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancel(int $id): JsonResponse
    {
        $subscription = $this->cancelService->cancel($id);

        return new JsonResponse(SubscriptionJson::from($subscription));
    }
}
