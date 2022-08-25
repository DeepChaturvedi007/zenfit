<?php

namespace AppBundle\Entity;

class LeadTag
{
    private ?int $id = null;
    private string $title;
    private Lead $lead;

    public function __construct(Lead $lead, string $title)
    {
        $this->lead = $lead;
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLead(): Lead
    {
        return $this->lead;
    }

    public function setLead(Lead $lead): self
    {
        $this->lead = $lead;

        return $this;
    }
}
