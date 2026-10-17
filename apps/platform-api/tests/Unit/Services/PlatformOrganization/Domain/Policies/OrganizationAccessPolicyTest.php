<?php

declare(strict_types=1);

namespace Tests\Unit\Services\PlatformOrganization\Domain\Policies;

use App\Services\PlatformOrganization\Domain\Policies\OrganizationAccessPolicy;
use PHPUnit\Framework\TestCase;

final class OrganizationAccessPolicyTest extends TestCase
{
    public function test_only_workspace_owner_and_admin_can_administer_workspace_organization(): void
    {
        self::assertSame(true, OrganizationAccessPolicy::canAdministerWorkspace('owner'));
        self::assertSame(true, OrganizationAccessPolicy::canAdministerWorkspace('admin'));
        self::assertSame(false, OrganizationAccessPolicy::canAdministerWorkspace('member'));
        self::assertSame(false, OrganizationAccessPolicy::canAdministerWorkspace(null));
    }
}
