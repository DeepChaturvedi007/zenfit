<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Client;
use AppBundle\Entity\ClientFoodPreference;

class ClientFoodPreferencesService
{
    private EntityManagerInterface $em;
    private $client;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function findOrCreateClientFoodPreference(Client $client): ClientFoodPreference
    {
        $foodPreferenceEntity = $this->em
            ->getRepository(ClientFoodPreference::class)
            ->findOneBy([
                'client' => $client
            ]);

        if(!$foodPreferenceEntity) {
            $foodPreferenceEntity = (new ClientFoodPreference($this->client));
            $this->em->persist($foodPreferenceEntity);
        }

        return $foodPreferenceEntity;
    }

    public function updateClientFoodPreferences(array $data): self
    {
        $foodPreferenceEntity = $this->findOrCreateClientFoodPreference($this->client);

        $columns = $this->getColumns();
        foreach($data as $item) {
            $setter = 'set'.ucfirst($item);
            $columns[$setter] = 1;
        }

        foreach($columns as $setter => $val) {
            if (method_exists($foodPreferenceEntity, $setter)) {
                $foodPreferenceEntity->$setter($val);
            }
        }

        $this->em->flush();

        return $this;
    }

    public function updateExcludeIngredients(?string $excludeIngredients): void
    {
        $foodPreferenceEntity = $this->findOrCreateClientFoodPreference($this->client);

        $foodPreferenceEntity->setExcludeIngredients($excludeIngredients);
        $this->em->flush();
    }

    private function getColumns() {
        return [
          'setAvoidLactose' => 0,
          'setAvoidGluten' => 0,
          'setAvoidNuts' => 0,
          'setAvoidEggs' => 0,
          'setAvoidPig' => 0,
          'setAvoidShellfish' => 0,
          'setAvoidFish' => 0,
          'setIsVegetarian' => 0,
          'setIsVegan' => 0,
          'setIsPescetarian' => 0
        ];
    }
}
