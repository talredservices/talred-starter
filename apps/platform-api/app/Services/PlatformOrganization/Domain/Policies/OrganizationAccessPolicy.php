<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\Domain\Policies;

final class OrganizationAccessPolicy
{
    public static function canAdministerWorkspace(?string $workspaceRole): bool
    {
        return in_array($workspaceRole, ['owner', 'admin'], true);
    }
}
