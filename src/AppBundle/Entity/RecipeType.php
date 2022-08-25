<?php declare(strict_types=1);

namespace AppBundle\Entity;

class RecipeType
{
    private ?int $id = null;
    private int $type;
    private Recipe $recipe;

    public function __construct(Recipe $recipe, int $type)
    {
        $this->recipe = $recipe;
        $this->type = $type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
