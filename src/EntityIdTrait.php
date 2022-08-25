<?php declare(strict_types=1);

namespace App;

trait EntityIdTrait
{
    private ?int $id = null;

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \RuntimeException('This entity is not persisted to DB yet');
        }

        return $this->id;
    }
}
