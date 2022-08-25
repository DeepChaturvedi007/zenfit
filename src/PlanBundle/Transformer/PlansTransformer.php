<?php
namespace PlanBundle\Transformer;

use AppBundle\Entity\Bundle;
use AppBundle\Entity\Client;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Plan;
use DateTime;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class PlansTransformer extends TransformerAbstract
{
    /**
     * @var Collection
     */
    private $entities;

    public function __construct(Collection $entities = null)
    {
        $this->entities = $entities;
    }

    /**
     * @param Plan $entity
     * @return array
     */
    public function transform(Plan $entity)
    {
        /** @var Client $clientInstance */
        $clientInstance = $entity->getClient();
        /** @var Payment $paymentInstance */
        $paymentInstance = $entity->getPayment();
        /** @var Bundle $bundleInstance */
        $bundleInstance = $entity->getBundle();

        $mealUpdated = $clientInstance->getMealUpdated();
        $workoutUpdated = $clientInstance->getWorkoutUpdated();
        $client = [
            'id' => $clientInstance->getId(),
            'name' => $clientInstance->getName(),
            'email' => $clientInstance->getEmail(),
            'mealUpdated' => $mealUpdated ? $mealUpdated->format(DateTime::ATOM) : null,
            'workoutUpdated' => $workoutUpdated ? $workoutUpdated->format(DateTime::ATOM) : null,
        ];

        $payment = [
            'id' => $paymentInstance->getId(),
            'recurringFee' => $paymentInstance->getRecurringFee(),
            'upfrontFee' => $paymentInstance->getUpfrontFee(),
            'currency' => $paymentInstance->getCurrency(),
            'charged' => $paymentInstance->getCharged(),
        ];

        $bundle = [
            'id' => $bundleInstance->getId(),
            'name' => $bundleInstance->getName(),
            'recurringFee' => $bundleInstance->getRecurringFee(),
            'upfrontFee' => $bundleInstance->getUpfrontFee(),
            'currency' => $bundleInstance->getCurrency(),
            'type' => $bundleInstance->getType()
        ];

        return [
            'id' => $entity->getId(),
            'client' => $client,
            'payment' => $payment,
            'bundle' => $bundle,
            'type' => $entity->getType(),
            'title' => $entity->getTitle(),
            'contacted' => $entity->getContacted(),
            'createdAt' => $entity->getCreatedAt()->format(DateTime::ATOM)
        ];
    }

    /**
     * @return array
     */
    public function getTransformedCollection()
    {
        return $this->entities->map(function ($item) {
            /** @var Plan $item */
            return $this->transform($item);
        })->toArray();
    }
}
