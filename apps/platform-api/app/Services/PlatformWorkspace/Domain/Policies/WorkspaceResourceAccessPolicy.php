<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Policies;

final class WorkspaceResourceAccessPolicy
{
    public static function canManage(string $workspaceRole): bool
    {
        return in_array($workspaceRole, ['owner', 'admin'], true);
    }
}
