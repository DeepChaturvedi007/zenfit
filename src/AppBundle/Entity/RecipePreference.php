<?php declare(strict_types=1);

namespace AppBundle\Entity;

class RecipePreference
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $favorite = false;

    /**
     * @var bool
     */
    private $dislike = false;

    /**
     * @var \AppBundle\Entity\Recipe
     */
    private $recipe;

    /**
     * @var \AppBundle\Entity\User
     */
    private $user;


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
     * Set favorite.
     *
     * @param bool $favorite
     *
     * @return RecipePreference
     */
    public function setFavorite($favorite)
    {
        $this->favorite = $favorite;

        return $this;
    }

    /**
     * Get favorite.
     *
     * @return bool
     */
    public function getFavorite()
    {
        return $this->favorite;
    }

    /**
     * Set dislike.
     *
     * @param bool $dislike
     *
     * @return RecipePreference
     */
    public function setDislike($dislike)
    {
        $this->dislike = $dislike;

        return $this;
    }

    /**
     * Get dislike.
     *
     * @return bool
     */
    public function getDislike()
    {
        return $this->dislike;
    }

    /**
     * Set recipe.
     *
     * @param \AppBundle\Entity\Recipe|null $recipe
     *
     * @return RecipePreference
     */
    public function setRecipe(\AppBundle\Entity\Recipe $recipe = null)
    {
        $this->recipe = $recipe;

        return $this;
    }

    /**
     * Get recipe.
     *
     * @return \AppBundle\Entity\Recipe|null
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

    /**
     * Set user.
     *
     * @param \AppBundle\Entity\User|null $user
     *
     * @return RecipePreference
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \AppBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
