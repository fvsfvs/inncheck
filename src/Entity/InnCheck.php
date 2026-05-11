<?php


declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'inn_checks')]
class InnCheck
{
    #[ORM\Id]
    #[ORM\Column(length: 12)]
    private string $inn;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $checkedAt;

    public function getInn(): string
    {
        return $this->inn;
    }

    public function setInn(string $inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    public function getCheckedAt(): DateTimeImmutable
    {
        return $this->checkedAt;
    }

    public function setCheckedAt(DateTimeImmutable $checkedAt): self
    {
        $this->checkedAt = $checkedAt;

        return $this;
    }

    public function isExpired(int $ttlDays): bool
    {
        return $this->checkedAt < new DateTimeImmutable("-{$ttlDays} days");
    }
}
