<?php declare(strict_types=1);

namespace AppBundle\Consumer;

class PdfGenerationEvent
{
    private int $type;
    private int $planId;
    private ?string $name;
    private string $clientEmail;
    private int $version;

    public function __construct(
        int $type,
        int $planId,
        ?string $name,
        string $clientEmail,
        int $version
    ) {
        $this->type = $type;
        $this->planId = $planId;
        $this->name = $name;
        $this->clientEmail = $clientEmail;
        $this->version = $version;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function getPlanId(): int
    {
        return $this->planId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
