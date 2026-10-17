<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Enums;

enum MemberRole: string
{
    case Owner = 'owner';
    case Administrator = 'admin';
    case Member = 'member';
}
