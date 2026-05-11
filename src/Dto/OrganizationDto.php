<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\InnCheck;

class OrganizationDto
{
    public InnCheck $innCheck;
    public string $ogrn;
    public string $name;
    public string $okved;
    public string $okvedType;
    public bool $isActive;
}
