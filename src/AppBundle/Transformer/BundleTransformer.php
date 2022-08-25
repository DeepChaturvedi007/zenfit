<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\Bundle;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class BundleTransformer extends TransformerAbstract
{
    /**
     * @var ?Collection<Bundle>
     */
    private ?Collection $entities;

    public function __construct(?Collection $bundles = null)
    {
        $this->entities = $bundles;
    }

    public function transform(Bundle $entity): array
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'description' => $entity->getDescription(),
            'upfrontFee' => $entity->getUpfrontFee(),
            'recurringFee' => $entity->getRecurringFee(),
            'currency' => $entity->getCurrency(),
            'type' => $entity->getType(),
        ];
    }

    /** @return array<mixed> */
    public function getTransformedCollection(): array
    {
        if ($this->entities === null) {
            return [];
        }

        return $this->entities->map(function ($item) {
            /** @var Bundle $item */
            return $this->transform($item);
        })->toArray();
    }
}
