<?php

declare(strict_types=1);

namespace Tests\Feature\Services\PlatformOrganization;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Contracts\PlatformOrganizationManagement;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourceAlreadyLinked;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourcePermissionDenied;
use App\Services\PlatformWorkspace\Application\Services\WorkspaceIdentityProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Fakes\FakeIdentityApiGateway;
use Tests\TestCase;

final class ManagePlatformOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private FakeIdentityApiGateway $identity;

    private PlatformOrganizationManagement $organization;

    private WorkspaceIdentityProjectService $workspaceResources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->identity = new FakeIdentityApiGateway();
        $this->app->instance(IdentityApiGateway::class, $this->identity);
        config(['services.platform_onboarding.login_project_slug' => 'talred-platform']);
        $this->organization = $this->app->make(PlatformOrganizationManagement::class);
        $this->workspaceResources = $this->app->make(WorkspaceIdentityProjectService::class);
    }

    public function test_workspace_membership_is_the_access_boundary_and_roles_are_workspace_scoped(): void
    {
        [$workspaceId, $ownerId, $memberId] = $this->workspaceFixture();
        $otherWorkspaceId = $this->createWorkspace($ownerId);
        $this->addMember($workspaceId, $memberId, 'member');

        $members = $this->organization->workspaceMembers($workspaceId, $memberId)['members'];
        $this->assertEqualsCanonicalizing([$ownerId, $memberId], array_column($members, 'account_id'));
        $this->assertHttpStatus(404, fn () => $this->organization->workspaceMembers($otherWorkspaceId, $memberId));
        $identityProjectId = (string) Str::uuid();
        $this->assertThrows(fn () => $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $identityProjectId, $memberId, 'user-access-token'), WorkspaceResourcePermissionDenied::class);

        DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $memberId)->update(['role' => 'admin']);
        $this->identity->identityProjects = [['id' => $identityProjectId, 'name' => 'Allowed', 'slug' => 'allowed', 'status' => 'active']];
        $linked = $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $identityProjectId, $memberId, 'user-access-token');
        $this->assertSame($identityProjectId, $linked['resource']['external_resource_id']);
        $this->assertDatabaseHas('platform_workspace_resources', [
            'id' => $linked['resource']['id'],
            'workspace_id' => $workspaceId,
            'resource_type' => 'identity_project',
            'external_resource_id' => $identityProjectId,
        ]);

        $this->organization->removeWorkspaceMember($workspaceId, $ownerId, $memberId);
        $this->assertHttpStatus(404, fn () => $this->organization->workspaceMembers($workspaceId, $memberId));
    }

    public function test_workspace_invitation_grants_only_the_invited_workspace_after_email_match(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $otherWorkspaceId = $this->createWorkspace($ownerId);
        $invite = $this->organization->createWorkspaceInvitation($workspaceId, $ownerId, 'New.Member@example.com', 'member');
        $newAccountId = (string) Str::uuid();
        $this->identity->identity = ['user_id' => $newAccountId, 'email' => 'new.member@example.com', 'project_slug' => 'talred-platform'];

        $accepted = $this->organization->acceptWorkspaceInvitation($invite['invite_token'], 'user-access-token');

        $this->assertSame($workspaceId, $accepted['workspace']['id']);
        $this->assertSame('member', $accepted['workspace']['role']);
        $this->assertDatabaseHas('platform_workspace_memberships', ['workspace_id' => $workspaceId, 'account_id' => $newAccountId, 'role' => 'member', 'status' => 'active']);
        $this->assertDatabaseMissing('platform_workspace_memberships', ['workspace_id' => $otherWorkspaceId, 'account_id' => $newAccountId]);
    }

    public function test_workspace_invitation_email_mismatch_does_not_create_access(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $invite = $this->organization->createWorkspaceInvitation($workspaceId, $ownerId, 'invited@example.com', 'member');
        $accountId = (string) Str::uuid();
        $this->identity->identity = ['user_id' => $accountId, 'email' => 'other@example.com', 'project_slug' => 'talred-platform'];

        $this->assertHttpStatus(403, fn () => $this->organization->acceptWorkspaceInvitation($invite['invite_token'], 'user-access-token'));
        $this->assertDatabaseMissing('platform_workspace_memberships', ['workspace_id' => $workspaceId, 'account_id' => $accountId]);
        $this->assertDatabaseHas('platform_workspace_invitations', ['id' => $invite['id'], 'accepted_at' => null]);
    }

    public function test_failed_identity_onboarding_does_not_create_a_workspace_invitation(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $this->identity->failInvitationIssue = true;

        $this->assertHttpStatus(503, fn () => $this->organization->createWorkspaceInvitation($workspaceId, $ownerId, 'no-access@example.com', 'member'));
        $this->assertDatabaseCount('platform_workspace_invitations', 0);
    }

    public function test_revoking_workspace_invitation_revokes_the_linked_login_invitation(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $invite = $this->organization->createWorkspaceInvitation($workspaceId, $ownerId, 'revoke@example.com', 'member');

        $revoked = $this->organization->revokeWorkspaceInvitation($workspaceId, $invite['id'], $ownerId);

        $this->assertTrue($revoked['identity_login_invitation_revoked']);
        $this->assertCount(1, $this->identity->revokedInvitations);
        $this->assertNotNull(DB::table('platform_workspace_invitations')->where('id', $invite['id'])->value('revoked_at'));
    }

    public function test_expired_and_revoked_workspace_invitations_cannot_create_memberships(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $expired = $this->organization->createWorkspaceInvitation($workspaceId, $ownerId, 'expired@example.com', 'member');
        DB::table('platform_workspace_invitations')->where('id', $expired['id'])->update(['expires_at' => now()->subSecond()]);
        $this->identity->identity = ['user_id' => (string) Str::uuid(), 'email' => 'expired@example.com', 'project_slug' => 'talred-platform'];
        $this->assertHttpStatus(404, fn () => $this->organization->acceptWorkspaceInvitation($expired['invite_token'], 'user-access-token'));

        $revoked = $this->organization->createWorkspaceInvitation($workspaceId, $ownerId, 'revoked@example.com', 'member');
        $this->organization->revokeWorkspaceInvitation($workspaceId, $revoked['id'], $ownerId);
        $accountId = (string) Str::uuid();
        $this->identity->identity = ['user_id' => $accountId, 'email' => 'revoked@example.com', 'project_slug' => 'talred-platform'];
        $this->assertHttpStatus(404, fn () => $this->organization->acceptWorkspaceInvitation($revoked['invite_token'], 'user-access-token'));
        $this->assertDatabaseMissing('platform_workspace_memberships', ['workspace_id' => $workspaceId, 'account_id' => $accountId]);
    }

    public function test_ownership_transfer_keeps_exactly_one_active_workspace_owner(): void
    {
        [$workspaceId, $ownerId, $memberId] = $this->workspaceFixture();
        $this->addMember($workspaceId, $memberId, 'member');

        $result = $this->organization->transferWorkspaceOwnership($workspaceId, $ownerId, $memberId);

        $this->assertSame($memberId, $result['owner_account_id']);
        $this->assertSame(1, DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('role', 'owner')->where('status', 'active')->count());
        $this->assertDatabaseHas('platform_workspace_memberships', ['workspace_id' => $workspaceId, 'account_id' => $ownerId, 'role' => 'admin', 'status' => 'active']);
    }

    public function test_identity_project_is_a_direct_workspace_resource_and_unlink_detaches_only_the_reference(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $identityProjectId = (string) Str::uuid();
        $this->identity->identityProjects = [['id' => $identityProjectId, 'name' => 'Identity Project', 'slug' => 'identity-project', 'status' => 'active']];
        $linked = $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $identityProjectId, $ownerId, 'user-access-token');

        $summary = $this->workspaceResources->workspaceIdentityProjects($workspaceId, $ownerId, 'user-access-token')['projects'][0];
        $this->assertSame($identityProjectId, $summary['id']);
        $this->assertDatabaseHas('platform_workspace_resources', ['id' => $linked['resource']['id'], 'workspace_id' => $workspaceId]);

        $this->workspaceResources->unlinkIdentityProjectFromWorkspace($workspaceId, $identityProjectId, $ownerId);

        $this->assertDatabaseMissing('platform_workspace_resources', ['external_resource_id' => $identityProjectId]);
        $this->assertSame($identityProjectId, $this->identity->identityProjects[0]['id']);
    }

    public function test_identity_project_link_is_unique_until_detached(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $secondWorkspaceId = $this->createWorkspace();
        $secondOwnerId = (string) DB::table('platform_workspaces')->where('id', $secondWorkspaceId)->value('owner_id');
        $identityProjectId = (string) Str::uuid();
        $this->identity->identityProjects = [['id' => $identityProjectId, 'name' => 'Identity Project', 'slug' => 'identity-project', 'status' => 'active']];

        $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $identityProjectId, $ownerId, 'user-access-token');
        $this->assertThrows(fn () => $this->workspaceResources->linkIdentityProjectToWorkspace($secondWorkspaceId, $identityProjectId, $secondOwnerId, 'user-access-token'), WorkspaceResourceAlreadyLinked::class);
        $this->workspaceResources->unlinkIdentityProjectFromWorkspace($workspaceId, $identityProjectId, $ownerId);
        $linked = $this->workspaceResources->linkIdentityProjectToWorkspace($secondWorkspaceId, $identityProjectId, $secondOwnerId, 'user-access-token');

        $this->assertSame($identityProjectId, $linked['resource']['external_resource_id']);
        $this->assertDatabaseHas('platform_workspace_resources', ['workspace_id' => $secondWorkspaceId, 'external_resource_id' => $identityProjectId]);
    }

    public function test_identity_details_are_sanitized_and_forbidden_details_are_generic(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $identityProjectId = (string) Str::uuid();
        $this->identity->identityProjects = [['id' => $identityProjectId, 'name' => 'Private Identity Project', 'slug' => 'private-identity-project', 'status' => 'active']];
        $this->identity->projectDetails[$identityProjectId] = ['status' => 'available', 'project' => [
            'id' => $identityProjectId,
            'name' => 'Private Identity Project',
            'slug' => 'private-identity-project',
            'status' => 'active',
            'clients' => [['id' => 'client-id', 'name' => 'Private Client', 'secret_prefix' => 'prefix', 'secret' => 'never-return-this']],
            'hosted_applications' => [['id' => 'application-id', 'name' => 'Portal', 'key' => 'portal', 'status' => 'active', 'application_url' => 'https://example.test', 'client_secret' => 'never-return-this']],
        ]];
        $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $identityProjectId, $ownerId, 'user-access-token');

        $summary = $this->workspaceResources->workspaceIdentityProjects($workspaceId, $ownerId, 'user-access-token')['projects'][0];
        $this->assertSame('Private Identity Project', $summary['identity_project']['name']);
        $this->assertArrayNotHasKey('secret', $summary['clients'][0]);
        $this->assertArrayNotHasKey('client_secret', $summary['applications'][0]);

        $this->identity->projectDetails[$identityProjectId] = ['status' => 'access_needed'];
        $generic = $this->workspaceResources->workspaceIdentityProjects($workspaceId, $ownerId, 'user-access-token')['projects'][0];
        $this->assertSame(['id' => $identityProjectId, 'availability' => 'access_needed'], $generic);
    }

    public function test_partial_identity_detail_failure_and_upstream_outage_are_reported_without_leaking_details(): void
    {
        [$workspaceId, $ownerId] = $this->workspaceFixture();
        $availableId = (string) Str::uuid();
        $unavailableId = (string) Str::uuid();
        $this->identity->identityProjects = [
            ['id' => $availableId, 'name' => 'Available', 'slug' => 'available', 'status' => 'active'],
            ['id' => $unavailableId, 'name' => 'Unavailable', 'slug' => 'unavailable', 'status' => 'active'],
        ];
        $this->identity->projectDetails[$availableId] = ['status' => 'available', 'project' => ['id' => $availableId, 'name' => 'Available']];
        $this->identity->projectDetailFailures[$unavailableId] = 'unavailable';
        $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $availableId, $ownerId, 'user-access-token');
        $this->workspaceResources->linkIdentityProjectToWorkspace($workspaceId, $unavailableId, $ownerId, 'user-access-token');

        $summary = $this->workspaceResources->workspaceIdentityProjects($workspaceId, $ownerId, 'user-access-token')['projects'];
        $this->assertCount(2, $summary);
        $this->assertSame('available', $summary[0]['availability']);
        $this->assertSame('unavailable', $summary[1]['availability']);

        $this->identity->projectListFailure = 'unavailable';
        $result = $this->workspaceResources->availableIdentityProjects($workspaceId, $ownerId, 'user-access-token');
        $this->assertSame('unavailable', $result['availability']);
        $this->assertSame([], $result['projects']);
    }

    private function workspaceFixture(): array
    {
        $workspaceId = $this->createWorkspace();
        $ownerId = (string) DB::table('platform_workspaces')->where('id', $workspaceId)->value('owner_id');
        return [$workspaceId, $ownerId, (string) Str::uuid()];
    }

    private function createWorkspace(?string $ownerId = null): string
    {
        $workspaceId = (string) Str::uuid();
        $ownerId ??= (string) Str::uuid();
        $now = now();
        DB::table('platform_workspaces')->insert([
            'id' => $workspaceId,
            'owner_id' => $ownerId,
            'name' => 'Workspace',
            'slug' => 'workspace-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->addMember($workspaceId, $ownerId, 'owner');
        return $workspaceId;
    }

    private function addMember(string $workspaceId, string $accountId, string $role): void
    {
        DB::table('platform_workspace_memberships')->insert([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'account_id' => $accountId,
            'role' => $role,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertHttpStatus(int $status, callable $callback): void
    {
        try {
            $callback();
            self::fail("Expected HTTP {$status}.");
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->getStatusCode());
        }
    }

}
