<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TaskManagerService;

use App\Services\TaskManagerService\Infrastructure\Models\ProjectRecord;
use App\Services\TaskManagerService\Infrastructure\Models\TaskRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TaskHttpFailuresTest extends TestCase
{
    use RefreshDatabase;

    private string $ownerId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ownerId = (string) Str::uuid();
        config(['zolta.identity_consumer.connections' => ['live' => [
            'base_url' => 'http://identity.test',
            'project' => 'tasks',
            'client_id' => 'test-client',
            'client_secret' => 'test-only-client-secret',
        ]]]);
        Http::preventStrayRequests();
        Http::fake(['identity.test/*' => Http::response([
            'active' => true,
            'sub' => $this->ownerId,
            'project_id' => (string) Str::uuid(),
            'project_slug' => 'tasks',
            'client_id' => 'test-client',
            'permissions' => ['account.read', 'account.update'],
            'exp' => now()->addMinute()->getTimestamp(),
        ])]);
        $this->withToken('test-only-user-token');
    }

    public function test_completed_task_cannot_reopen_and_returns_a_conflict_without_changing_it(): void
    {
        $project = $this->project($this->ownerId);
        $task = TaskRecord::query()->create([
            'project_id' => $project->id, 'owner_id' => $this->ownerId,
            'title' => 'Completed task', 'status' => 'done', 'priority' => 'medium',
        ]);

        $this->patchJson('/api/tasks/'.$task->id, ['status' => 'todo'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'A completed task cannot be moved back to an active status.');
        $this->assertDatabaseHas('project_tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_other_owners_project_and_absent_tasks_are_not_found(): void
    {
        $other = $this->project((string) Str::uuid());
        $this->getJson('/api/projects/'.$other->id.'/tasks')->assertNotFound();
        $this->patchJson('/api/tasks/'.Str::uuid(), ['status' => 'done'])->assertNotFound();
        $this->deleteJson('/api/projects/'.$other->id)->assertNotFound();
        $this->assertDatabaseHas('projects', ['id' => $other->id]);
    }

    public function test_duplicate_project_key_is_a_conflict_but_another_owner_can_use_it(): void
    {
        $this->project($this->ownerId);
        $this->postJson('/api/projects', ['name' => 'Duplicate', 'key' => 'fixture'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'You already have a project with this key. Choose another key.');
        $this->assertDatabaseCount('projects', 1);
        $this->project((string) Str::uuid(), 'other-key');
        $this->postJson('/api/projects', ['name' => 'Separate key', 'key' => 'other-key'])->assertCreated();
    }

    private function project(string $ownerId, string $key = 'fixture'): ProjectRecord
    {
        return ProjectRecord::query()->create(['owner_id' => $ownerId, 'name' => 'Fixture', 'key' => $key, 'status' => 'active']);
    }
}
