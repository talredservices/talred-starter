<?php

declare(strict_types=1);

namespace Tests\Feature\Services\PlatformOrganization;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PlatformOrganizationMigrationTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = (string) config('database.default');
        config(['database.connections.platform_migration_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        config(['database.default' => 'platform_migration_test']);
        DB::purge('platform_migration_test');
        $this->createLegacyWorkspaceSchema();
    }

    protected function tearDown(): void
    {
        DB::purge('platform_migration_test');
        config(['database.default' => $this->originalConnection]);

        parent::tearDown();
    }

    public function test_workspace_migration_removes_teams_and_preserves_workspace_ids_defaults_and_resources(): void
    {
        $ownerId = (string) Str::uuid();
        $memberId = (string) Str::uuid();
        $workspaceId = (string) Str::uuid();
        $secondWorkspaceId = (string) Str::uuid();
        $this->insertLegacyWorkspace($workspaceId, $ownerId, $memberId);

        DB::table('platform_workspaces')->insert([
            'id' => $secondWorkspaceId,
            'owner_id' => $ownerId,
            'name' => 'Second workspace',
            'slug' => 'second-workspace',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('platform_workspace_memberships')->insert([
            'id' => (string) Str::uuid(),
            'workspace_id' => $secondWorkspaceId,
            'account_id' => $memberId,
            'role' => 'administrator',
            'status' => 'removed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $organizationMigration = require base_path('app/Services/PlatformWorkspace/Infrastructure/Persistence/Migrations/2026_10_11_000002_create_platform_organization_tables.php');
        $organizationMigration->up();
        $teamId = (string) DB::table('platform_workspaces')->where('id', $workspaceId)->value('team_id');
        DB::table('platform_team_invitations')->insert([
            'id' => (string) Str::uuid(),
            'team_id' => $teamId,
            'email' => 'pending@example.com',
            'role' => 'member',
            'token_hash' => hash('sha256', 'legacy-team-invitation'),
            'identity_invitation_token' => 'encrypted-at-rest-by-the-application',
            'created_by' => $ownerId,
            'expires_at' => now()->addHours(72),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $platformProjectId = (string) Str::uuid();
        $identityProjectId = (string) Str::uuid();
        $secondIdentityProjectId = (string) Str::uuid();
        DB::table('platform_projects')->insert([
            'id' => $platformProjectId,
            'workspace_id' => $workspaceId,
            'name' => 'Existing platform project',
            'slug' => 'existing-platform-project',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $archivedPlatformProjectId = (string) Str::uuid();
        DB::table('platform_projects')->insert([
            'id' => $archivedPlatformProjectId,
            'workspace_id' => $workspaceId,
            'name' => 'Archived platform project',
            'slug' => 'archived-platform-project',
            'status' => 'archived',
            'archived_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('platform_identity_project_links')->insert([
            'id' => (string) Str::uuid(),
            'platform_project_id' => $platformProjectId,
            'identity_project_id' => $identityProjectId,
            'linked_by' => $ownerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('platform_identity_project_links')->insert([
            'id' => (string) Str::uuid(),
            'platform_project_id' => $archivedPlatformProjectId,
            'identity_project_id' => $secondIdentityProjectId,
            'linked_by' => $ownerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $workspaceOnlyMigration = require base_path('app/Services/PlatformWorkspace/Infrastructure/Persistence/Migrations/2026_10_11_000003_scope_memberships_and_invitations_to_workspaces.php');
        $workspaceOnlyMigration->up();
        $resourceMigration = require base_path('app/Services/PlatformWorkspace/Infrastructure/Persistence/Migrations/2026_10_12_000001_replace_platform_projects_with_workspace_resources.php');
        $resourceMigration->up();

        $workspace = DB::table('platform_workspaces')->where('id', $workspaceId)->first();
        $this->assertSame($workspaceId, (string) $workspace->id);
        $this->assertDatabaseHas('platform_workspaces', ['id' => $secondWorkspaceId]);
        $this->assertTrue(Schema::hasTable('platform_workspace_invitations'));
        $this->assertFalse(Schema::hasTable('platform_teams'));
        $this->assertFalse(Schema::hasTable('platform_team_memberships'));
        $this->assertFalse(Schema::hasTable('platform_team_invitations'));
        $this->assertFalse(Schema::hasColumn('platform_workspaces', 'team_id'));
        $this->assertDatabaseHas('platform_account_workspace_defaults', [
            'account_id' => $ownerId,
            'workspace_id' => $workspaceId,
        ]);
        $this->assertDatabaseHas('platform_workspace_memberships', [
            'workspace_id' => $workspaceId,
            'account_id' => $ownerId,
            'role' => 'owner',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('platform_workspace_memberships', [
            'workspace_id' => $workspaceId,
            'account_id' => $memberId,
            'role' => 'admin',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('platform_workspace_memberships', [
            'workspace_id' => $secondWorkspaceId,
            'account_id' => $memberId,
            'role' => 'admin',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('platform_workspace_resources', [
            'workspace_id' => $workspaceId,
            'resource_type' => 'identity_project',
            'external_resource_id' => $identityProjectId,
            'linked_by' => $ownerId,
        ]);
        $this->assertDatabaseHas('platform_workspace_resources', [
            'workspace_id' => $workspaceId,
            'resource_type' => 'identity_project',
            'external_resource_id' => $secondIdentityProjectId,
            'linked_by' => $ownerId,
        ]);
        $this->assertSame(2, DB::table('platform_workspace_resources')->where('workspace_id', $workspaceId)->count());
        $this->assertFalse(Schema::hasTable('platform_projects'));
        $this->assertFalse(Schema::hasTable('platform_identity_project_links'));
        $this->assertDatabaseHas('platform_projects_legacy', ['id' => $platformProjectId, 'workspace_id' => $workspaceId]);
        $this->assertDatabaseHas('platform_identity_project_links_legacy', ['platform_project_id' => $archivedPlatformProjectId, 'identity_project_id' => $secondIdentityProjectId]);

        $resourceMigration->down();
        $this->assertFalse(Schema::hasTable('platform_workspace_resources'));
        $this->assertDatabaseHas('platform_projects', ['id' => $platformProjectId, 'workspace_id' => $workspaceId]);
        $this->assertDatabaseHas('platform_identity_project_links', ['platform_project_id' => $archivedPlatformProjectId, 'identity_project_id' => $secondIdentityProjectId]);
    }

    private function createLegacyWorkspaceSchema(): void
    {
        Schema::create('platform_workspaces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('owner_id')->index();
            $table->string('name', 120);
            $table->string('slug', 64)->unique();
            $table->string('status', 32)->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('platform_workspace_memberships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->index();
            $table->uuid('account_id')->index();
            $table->string('role', 32)->default('member');
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->unique(['workspace_id', 'account_id']);
        });
        Schema::create('platform_account_workspace_defaults', function (Blueprint $table): void {
            $table->uuid('account_id')->primary();
            $table->uuid('workspace_id');
        });
    }

    private function insertLegacyWorkspace(string $workspaceId, string $ownerId, string $memberId): void
    {
        $now = now();
        DB::table('platform_workspaces')->insert([
            'id' => $workspaceId,
            'owner_id' => $ownerId,
            'name' => 'My workspace',
            'slug' => 'my-workspace',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('platform_workspace_memberships')->insert([
            ['id' => (string) Str::uuid(), 'workspace_id' => $workspaceId, 'account_id' => $ownerId, 'role' => 'owner', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => (string) Str::uuid(), 'workspace_id' => $workspaceId, 'account_id' => $memberId, 'role' => 'administrator', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('platform_account_workspace_defaults')->insert([
            'account_id' => $ownerId,
            'workspace_id' => $workspaceId,
        ]);
    }
}
