<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Document
{
    use EntityIdTrait;

    private string $name;
    private string $fileName;
    private ?string $comment = null;
    private ?string $image = null;
    private ?string $assignmentTags = null;
    private bool $demo = false;
    private bool $deleted = false;
    private ?User $user = null;
    private ?Bundle $bundle = null;
    private Collection $client;

    public function __construct(string $name, string $fileName)
    {
        $this->name = $name;
        $this->fileName = $fileName;
        $this->client = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setComment(?string $comment = null): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setDemo(bool $demo): self
    {
        $this->demo = $demo;
        return $this;
    }

    public function getDemo(): bool
    {
        return $this->demo;
    }

    public function setUser(User $user = null): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        if ($this->user === null) {
            throw new \RuntimeException('This is demo document, no user attached');
        }

        return $this->user;
    }

    public function setBundle(?Bundle $bundle): self
    {
        $this->bundle = $bundle;
        return $this;
    }

    public function getBundle(): ?Bundle
    {
        return $this->bundle;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setAssignmentTags(array $assignmentTags = []): self
    {
        $data = ['tags' => $assignmentTags];
        $this->assignmentTags = json_encode($data); /* @phpstan-ignore-line */
        return $this;
    }

    /** @return mixed[] */
    public function getAssignmentTags(): array
    {
        $transformFn = function ($value) {
            return ['title' => $value];
        };

        if ($this->assignmentTags === null) {
            return [];
        }

        $data = json_decode($this->assignmentTags, true);
        if (!$data) return [];
        $tagsArray = is_array($data['tags']) ? $data['tags'] : [];
        return array_map($transformFn, array_filter($tagsArray));
    }

    public function isAssignedToAll(): bool
    {
        $tags = $this->getAssignmentTags();
        return in_array('all', $tags) ||  in_array('#all', $tags);
    }

    public function setImage(?string $image = null): self
    {
        $this->image = $image;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
}
