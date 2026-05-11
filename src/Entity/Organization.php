<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'organizations')]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'inn', referencedColumnName: 'inn', nullable: false)]
    private InnCheck $innCheck;

    #[ORM\Column(length: 15, unique: true)]
    private string $ogrn;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 15)]
    private string $okved;

    #[ORM\Column(length: 15)]
    private string $okvedType;

    #[ORM\Column()]
    private bool $isActive;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInnCheck(): InnCheck
    {
        return $this->innCheck;
    }

    public function setInnCheck(InnCheck $innCheck): self
    {
        $this->innCheck = $innCheck;

        return $this;
    }

    public function getOgrn(): string
    {
        return $this->ogrn;
    }

    public function setOgrn(string $ogrn): self
    {
        $this->ogrn = $ogrn;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOkved(): string
    {
        return $this->okved;
    }

    public function setOkved(string $okved): self
    {
        $this->okved = $okved;

        return $this;
    }

    public function getOkvedType(): string
    {
        return $this->okvedType;
    }

    public function setOkvedType(string $okvedType): self
    {
        $this->okvedType = $okvedType;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new DateTimeImmutable();

        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
