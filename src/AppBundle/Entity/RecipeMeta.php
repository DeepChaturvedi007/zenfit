<?php

namespace AppBundle\Entity;

/**
 * RecipeMeta
 */
class RecipeMeta
{
    public function serialize() {
      return [
        'lactose' => $this->getLactose(),
        'gluten' => $this->getGluten(),
        'nuts' => $this->getNuts(),
        'eggs' => $this->getEggs(),
        'pig' => $this->getPig(),
        'shellfish' => $this->getShellfish(),
        'fish' => $this->getFish(),
        'isVegetarian' => $this->getIsVegetarian(),
        'isVegan' => $this->getIsVegan(),
        'isPescetarian' => $this->getIsPescetarian()
      ];
    }

    public function __construct(Recipe $recipe)
    {
        $this->recipe = $recipe;
    }

    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $lactose = false;

    /**
     * @var bool
     */
    private $gluten = false;

    /**
     * @var bool
     */
    private $nuts = false;

    /**
     * @var bool
     */
    private $eggs = false;

    /**
     * @var bool
     */
    private $pig = false;

    /**
     * @var bool
     */
    private $shellfish = false;

    /**
     * @var bool
     */
    private $fish = false;

    /**
     * @var bool
     */
    private $isVegetarian = false;

    /**
     * @var bool
     */
    private $isVegan = false;

    /**
     * @var bool
     */
    private $isPescetarian = false;

    private Recipe $recipe;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lactose.
     *
     * @param bool $lactose
     *
     * @return RecipeMeta
     */
    public function setLactose($lactose)
    {
        $this->lactose = $lactose;

        return $this;
    }

    /**
     * Get lactose.
     *
     * @return bool
     */
    public function getLactose()
    {
        return $this->lactose;
    }

    /**
     * Set gluten.
     *
     * @param bool $gluten
     *
     * @return RecipeMeta
     */
    public function setGluten($gluten)
    {
        $this->gluten = $gluten;

        return $this;
    }

    /**
     * Get gluten.
     *
     * @return bool
     */
    public function getGluten()
    {
        return $this->gluten;
    }

    /**
     * Set nuts.
     *
     * @param bool $nuts
     *
     * @return RecipeMeta
     */
    public function setNuts($nuts)
    {
        $this->nuts = $nuts;

        return $this;
    }

    /**
     * Get nuts.
     *
     * @return bool
     */
    public function getNuts()
    {
        return $this->nuts;
    }

    /**
     * Set eggs.
     *
     * @param bool $eggs
     *
     * @return RecipeMeta
     */
    public function setEggs($eggs)
    {
        $this->eggs = $eggs;

        return $this;
    }

    /**
     * Get eggs.
     *
     * @return bool
     */
    public function getEggs()
    {
        return $this->eggs;
    }

    /**
     * Set pig.
     *
     * @param bool $pig
     *
     * @return RecipeMeta
     */
    public function setPig($pig)
    {
        $this->pig = $pig;

        return $this;
    }

    /**
     * Get pig.
     *
     * @return bool
     */
    public function getPig()
    {
        return $this->pig;
    }

    /**
     * Set shellfish.
     *
     * @param bool $shellfish
     *
     * @return RecipeMeta
     */
    public function setShellfish($shellfish)
    {
        $this->shellfish = $shellfish;

        return $this;
    }

    /**
     * Get shellfish.
     *
     * @return bool
     */
    public function getShellfish()
    {
        return $this->shellfish;
    }

    /**
     * Set fish.
     *
     * @param bool $fish
     *
     * @return RecipeMeta
     */
    public function setFish($fish)
    {
        $this->fish = $fish;

        return $this;
    }

    /**
     * Get fish.
     *
     * @return bool
     */
    public function getFish()
    {
        return $this->fish;
    }

    /**
     * Set isVegetarian.
     *
     * @param bool $isVegetarian
     *
     * @return RecipeMeta
     */
    public function setIsVegetarian($isVegetarian)
    {
        $this->isVegetarian = $isVegetarian;

        return $this;
    }

    /**
     * Get isVegetarian.
     *
     * @return bool
     */
    public function getIsVegetarian()
    {
        return $this->isVegetarian;
    }

    /**
     * Set isVegan.
     *
     * @param bool $isVegan
     *
     * @return RecipeMeta
     */
    public function setIsVegan($isVegan)
    {
        $this->isVegan = $isVegan;

        return $this;
    }

    /**
     * Get isVegan.
     *
     * @return bool
     */
    public function getIsVegan()
    {
        return $this->isVegan;
    }

    /**
     * Set isPescetarian.
     *
     * @param bool $isPescetarian
     *
     * @return RecipeMeta
     */
    public function setIsPescetarian($isPescetarian)
    {
        $this->isPescetarian = $isPescetarian;

        return $this;
    }

    /**
     * Get isPescetarian.
     *
     * @return bool
     */
    public function getIsPescetarian()
    {
        return $this->isPescetarian;
    }

    public function setRecipe(\AppBundle\Entity\Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    /**
     * Get recipe.
     *
     * @return \AppBundle\Entity\Recipe
     */
    public function getRecipe()
    {
        return $this->recipe;
    }
}
