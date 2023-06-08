<?php

namespace App\Entity;

use App\Repository\CookieConsentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CookieConsentRepository::class)]
class CookieConsent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ipAddress = null;

    #[ORM\Column]
    private ?bool $analyticsConsent = null;

    #[ORM\Column]
    private ?bool $marketingConsent = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function isAnalyticsConsent(): ?bool
    {
        return $this->analyticsConsent;
    }

    public function setAnalyticsConsent(bool $analyticsConsent): self
    {
        $this->analyticsConsent = $analyticsConsent;

        return $this;
    }

    public function isMarketingConsent(): ?bool
    {
        return $this->marketingConsent;
    }

    public function setMarketingConsent(bool $marketingConsent): self
    {
        $this->marketingConsent = $marketingConsent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
