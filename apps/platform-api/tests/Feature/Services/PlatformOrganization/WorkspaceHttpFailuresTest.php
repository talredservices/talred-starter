<?php

declare(strict_types=1);

namespace Tests\Feature\Services\PlatformOrganization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class WorkspaceHttpFailuresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['zolta.identity_consumer.connections' => ['live' => [
            'base_url' => 'http://identity.test', 'project' => 'starter',
            'client_id' => 'test-client', 'client_secret' => 'test-only-client-secret',
        ]]]);
        Http::preventStrayRequests();
        Http::fake(['identity.test/*' => Http::response([
            'active' => true, 'sub' => (string) Str::uuid(),
            'project_id' => (string) Str::uuid(), 'project_slug' => 'starter',
            'client_id' => 'test-client', 'exp' => now()->addMinute()->getTimestamp(),
        ])]);
        $this->withToken('test-only-user-token');
    }

    public function test_duplicate_slug_returns_conflict_without_creating_another_workspace_or_membership(): void
    {
        $this->postJson('/api/platform/v1/workspaces', ['name' => 'First', 'slug' => 'shared-slug'])->assertCreated();
        $this->postJson('/api/platform/v1/workspaces', ['name' => 'Second', 'slug' => 'shared-slug'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'A workspace with this slug already exists.');
        $this->assertDatabaseCount('platform_workspaces', 1);
        $this->assertDatabaseCount('platform_workspace_memberships', 1);
    }

    public function test_missing_or_other_accounts_workspace_is_not_found(): void
    {
        $otherId = (string) Str::uuid();
        DB::table('platform_workspaces')->insert([
            'id' => $otherId, 'owner_id' => (string) Str::uuid(),
            'name' => 'Other account', 'slug' => 'other-account', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->getJson('/api/platform/v1/workspaces/'.$otherId)->assertNotFound();
        $this->getJson('/api/platform/v1/workspaces/'.Str::uuid())->assertNotFound();
    }
}
