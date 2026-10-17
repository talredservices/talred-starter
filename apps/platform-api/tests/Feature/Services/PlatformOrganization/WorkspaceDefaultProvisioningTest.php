<?php

declare(strict_types=1);

namespace Tests\Feature\Services\PlatformOrganization;

use App\Services\PlatformWorkspace\Application\Commands\EnsureDefaultWorkspaceCommand;
use App\Services\PlatformWorkspace\Application\Commands\EnsureDefaultWorkspaceCommandHandler;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceRepository;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class WorkspaceDefaultProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_workspace_provisioning_is_idempotent_and_creates_direct_owner_membership(): void
    {
        $accountId = (string) Str::uuid();
        $handler = new EnsureDefaultWorkspaceCommandHandler($this->app->make(WorkspaceRepository::class));
        $command = new EnsureDefaultWorkspaceCommand(AccountId::fromString($accountId));

        $first = $handler($command)->getOrFail()['workspace'];
        $second = $handler($command)->getOrFail()['workspace'];

        $this->assertSame('My workspace', $first->name);
        $this->assertSame($first->id, $second->id);
        $this->assertTrue($second->isDefault);
        $this->assertDatabaseCount('platform_workspaces', 1);
        $this->assertDatabaseHas('platform_workspace_memberships', [
            'workspace_id' => $first->id,
            'account_id' => $accountId,
            'role' => 'owner',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('platform_account_workspace_defaults', [
            'account_id' => $accountId,
            'workspace_id' => $first->id,
        ]);
    }

    public function test_existing_default_workspace_and_identifier_are_reused(): void
    {
        $accountId = (string) Str::uuid();
        $workspaceId = (string) Str::uuid();
        $now = now();
        DB::table('platform_workspaces')->insert([
            'id' => $workspaceId,
            'owner_id' => $accountId,
            'name' => 'My workspace',
            'slug' => 'my-workspace',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('platform_workspace_memberships')->insert([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'account_id' => $accountId,
            'role' => 'owner',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('platform_account_workspace_defaults')->insert([
            'account_id' => $accountId,
            'workspace_id' => $workspaceId,
        ]);

        $handler = new EnsureDefaultWorkspaceCommandHandler($this->app->make(WorkspaceRepository::class));
        $workspace = $handler(new EnsureDefaultWorkspaceCommand(AccountId::fromString($accountId)))->getOrFail()['workspace'];

        $this->assertSame($workspaceId, $workspace->id);
        $this->assertDatabaseCount('platform_workspaces', 1);
        $this->assertDatabaseHas('platform_account_workspace_defaults', ['account_id' => $accountId, 'workspace_id' => $workspaceId]);
    }
}
