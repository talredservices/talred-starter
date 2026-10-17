<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Enums;

enum WorkspaceStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
