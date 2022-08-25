<?php declare(strict_types=1);

namespace AppBundle\Entity;

class Language
{
    public const LOCALE_NO = 'nb_NO';
    public const LOCALE_SV = 'sv_SE';
    public const LOCALE_DK = 'da_DK';
    public const LOCALE_FI = 'fi_FI';
    public const LOCALE_NL = 'nl_NL';
    public const LOCALE_DE = 'de_DE';
    public const LOCALE_EN = 'en';

    private ?int $id = null;
    private string $name;
    private string $locale;

    public function __construct(string $name, string $locale)
    {
        $this->name = $name;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
