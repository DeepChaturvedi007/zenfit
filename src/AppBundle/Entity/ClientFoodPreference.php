<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class ClientFoodPreference
{
    use EntityIdTrait;

    private bool $avoidLactose = false;
    private bool $avoidGluten = false;
    private bool $avoidNuts = false;
    private bool $avoidEggs = false;
    private bool $avoidPig = false;
    private bool $avoidShellfish = false;
    private bool $avoidFish = false;
    private bool $isVegetarian = false;
    private bool $isVegan = false;
    private bool $isPescetarian = false;
    private ?string $excludeIngredients = null;
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /** @return array<int, string> */
    public function prepareData(): array
    {
        $response = [];
        $serialized = $this->serialize();
        foreach ($serialized as $key => $val) {
            if ($val === true) {
                $response[] = $key;
            }
        }
        return $response;
    }

    /** @return array<string, mixed> */
    public function serialize(): array
    {
        return [
            'avoidLactose' => $this->getAvoidLactose(),
            'avoidGluten' => $this->getAvoidGluten(),
            'avoidNuts' => $this->getAvoidNuts(),
            'avoidEggs' => $this->getAvoidEggs(),
            'avoidPig' => $this->getAvoidPig(),
            'avoidShellfish' => $this->getAvoidShellfish(),
            'avoidFish' => $this->getAvoidFish(),
            'isVegetarian' => $this->isVegetarian(),
            'isVegan' => $this->isVegan(),
            'isPescetarian' => $this->isPescetarian()
        ];
    }

    public function setAvoidLactose(bool $avoidLactose): self
    {
        $this->avoidLactose = $avoidLactose;
        return $this;
    }

    public function getAvoidLactose(): bool
    {
        return $this->avoidLactose;
    }

    public function setAvoidGluten(bool $avoidGluten): self
    {
        $this->avoidGluten = $avoidGluten;
        return $this;
    }

    public function getAvoidGluten(): bool
    {
        return $this->avoidGluten;
    }

    public function setAvoidNuts(bool $avoidNuts): self
    {
        $this->avoidNuts = $avoidNuts;
        return $this;
    }

    public function getAvoidNuts(): bool
    {
        return $this->avoidNuts;
    }

    public function setAvoidEggs(bool $avoidEggs): self
    {
        $this->avoidEggs = $avoidEggs;
        return $this;
    }

    public function getAvoidEggs(): bool
    {
        return $this->avoidEggs;
    }

    public function setAvoidPig(bool $avoidPig): self
    {
        $this->avoidPig = $avoidPig;
        return $this;
    }

    public function getAvoidPig(): bool
    {
        return $this->avoidPig;
    }

    public function setAvoidShellfish(bool $avoidShellfish): self
    {
        $this->avoidShellfish = $avoidShellfish;
        return $this;
    }

    public function getAvoidShellfish(): bool
    {
        return $this->avoidShellfish;
    }

    public function setAvoidFish(bool $avoidFish): self
    {
        $this->avoidFish = $avoidFish;
        return $this;
    }

    public function getAvoidFish(): bool
    {
        return $this->avoidFish;
    }

    public function setIsVegetarian(bool $isVegetarian): self
    {
        $this->isVegetarian = $isVegetarian;
        return $this;
    }

    public function isVegetarian(): bool
    {
        return $this->isVegetarian;
    }

    public function setIsVegan(bool $isVegan): self
    {
        $this->isVegan = $isVegan;
        return $this;
    }

    public function isVegan(): bool
    {
        return $this->isVegan;
    }

    public function setIsPescetarian(bool $isPescetarian): self
    {
        $this->isPescetarian = $isPescetarian;
        return $this;
    }

    public function isPescetarian(): bool
    {
        return $this->isPescetarian;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setExcludeIngredients(?string $excludeIngredients = null): self
    {
        $this->excludeIngredients = $excludeIngredients;
        return $this;
    }

    public function getExcludeIngredients(): ?string
    {
        return $this->excludeIngredients;
    }
}
