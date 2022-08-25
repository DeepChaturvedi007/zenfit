<?php

namespace AppBundle\Entity;

class MealProductWeight
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    private float $weight = 0;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var MealProduct
     */
    private $product;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MealProductWeight
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getWeight(): int
    {
        return (int) $this->weight;
    }

    public function setWeight(float|int $weight): self
    {
        $this->weight = (float) $weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return MealProductWeight
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return MealProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return MealProductWeight
     */
    public function setProduct(MealProduct $product)
    {
        $this->product = $product;
        return $this;
    }
}
