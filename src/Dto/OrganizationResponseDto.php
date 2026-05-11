<?php

declare(strict_types=1);

namespace App\Dto;

class OrganizationResponseDto
{
    public string $inn;
    public string $ogrn;
    public string $name;
    public string $okved;
    public string $okvedType;
    public bool $isActive;
}
