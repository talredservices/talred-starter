<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\Infrastructure\Services;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;
use App\Services\PlatformOrganization\Domain\Policies\OrganizationAccessPolicy;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class PlatformOrganizationManager implements \App\Services\PlatformOrganization\Application\Contracts\PlatformOrganizationManagement
{
    public function __construct(
        private IdentityApiGateway $identity,
    ) {}

    /** @return array{workspace: array<string, mixed>} */
    public function getWorkspace(string $workspaceId, string $accountId): array
    {
        return ['workspace' => $this->workspaceRole($workspaceId, $accountId)];
    }

    /** @return array{workspace: array<string, mixed>} */
    public function renameWorkspace(string $workspaceId, string $accountId, string $name): array
    {
        $this->requireWorkspaceAdmin($workspaceId, $accountId);
        $name = trim($name);
        abort_if($name === '' || mb_strlen($name) > 120, 422, 'Workspace name must be between 1 and 120 characters.');
        DB::table('platform_workspaces')->where('id', $workspaceId)->update(['name' => $name, 'updated_at' => now()]);
        return ['workspace' => $this->workspaceRole($workspaceId, $accountId)];
    }

    /** @return array{members: list<array<string, mixed>>} */
    public function workspaceMembers(string $workspaceId, string $accountId): array
    {
        $this->workspaceRole($workspaceId, $accountId);
        $members = DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('status', 'active')
            ->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")->orderBy('created_at')
            ->get(['account_id', 'role', 'created_at']);
        return ['members' => $members->map(static fn ($member): array => [
            'account_id' => (string) $member->account_id, 'role' => (string) $member->role, 'joined_at' => (string) $member->created_at,
        ])->all()];
    }

    /** @return array{account_id: string, role: string} */
    public function updateWorkspaceMember(string $workspaceId, string $accountId, string $targetAccountId, string $role): array
    {
        abort_unless(in_array($role, ['admin', 'member'], true), 422, 'Workspace members can only be admins or members.');
        $this->requireWorkspaceAdmin($workspaceId, $accountId);
        $target = DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $targetAccountId)->where('status', 'active')->first();
        abort_if($target === null, 404, 'Workspace member not found.');
        abort_if($target->role === 'owner', 422, 'Transfer Workspace ownership before changing the owner role.');
        DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $targetAccountId)->update(['role' => $role, 'updated_at' => now()]);
        return ['account_id' => $targetAccountId, 'role' => $role];
    }

    /** @return array{account_id: string, status: string} */
    public function removeWorkspaceMember(string $workspaceId, string $accountId, string $targetAccountId): array
    {
        DB::transaction(function () use ($workspaceId, $accountId, $targetAccountId): void {
            $this->requireWorkspaceAdmin($workspaceId, $accountId);
            $target = DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $targetAccountId)->where('status', 'active')->lockForUpdate()->first();
            abort_if($target === null, 404, 'Workspace member not found.');
            abort_if($target->role === 'owner', 422, 'Transfer Workspace ownership before removing the owner.');
            DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $targetAccountId)->update(['status' => 'removed', 'updated_at' => now()]);
        });
        return ['account_id' => $targetAccountId, 'status' => 'removed'];
    }

    /** @return array{workspace_id: string, owner_account_id: string} */
    public function transferWorkspaceOwnership(string $workspaceId, string $accountId, string $targetAccountId): array
    {
        abort_if($accountId === $targetAccountId, 422, 'The selected account already owns this Workspace.');
        DB::transaction(function () use ($workspaceId, $accountId, $targetAccountId): void {
            $workspace = DB::table('platform_workspaces')->where('id', $workspaceId)->whereNull('archived_at')->lockForUpdate()->first();
            abort_if($workspace === null, 404, 'Workspace not found.');
            $owner = DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $accountId)->where('role', 'owner')->where('status', 'active')->lockForUpdate()->first();
            abort_if($owner === null || (string) $workspace->owner_id !== $accountId, 403, 'Only the Workspace owner can transfer ownership.');
            $target = DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('account_id', $targetAccountId)->where('status', 'active')->lockForUpdate()->first();
            abort_if($target === null, 404, 'The new owner must already be an active Workspace member.');
            $now = now();
            DB::table('platform_workspace_memberships')->where('id', $owner->id)->update(['role' => 'admin', 'updated_at' => $now]);
            DB::table('platform_workspace_memberships')->where('id', $target->id)->update(['role' => 'owner', 'updated_at' => $now]);
            DB::table('platform_workspaces')->where('id', $workspaceId)->update(['owner_id' => $targetAccountId, 'updated_at' => $now]);
        });
        return ['workspace_id' => $workspaceId, 'owner_account_id' => $targetAccountId];
    }

    /** @return array<string, mixed> */
    public function createWorkspaceInvitation(string $workspaceId, string $accountId, string $email, string $role): array
    {
        $this->requireWorkspaceAdmin($workspaceId, $accountId);
        abort_unless(in_array($role, ['admin', 'member'], true), 422, 'Invitations can only assign admin or member roles.');
        $email = mb_strtolower(trim($email));
        abort_if(! filter_var($email, FILTER_VALIDATE_EMAIL), 422, 'Invitation email is invalid.');
        try {
            $identityInvitation = $this->identity->issueLoginInvitation($email);
        } catch (DecryptException|RuntimeException) {
            abort(503, 'Platform login onboarding is temporarily unavailable. No Workspace invitation was created.');
        }
        $identityInvitationToken = (string) ($identityInvitation['invitation_token'] ?? '');
        abort_unless($identityInvitation['already_a_member'] || $identityInvitationToken !== '', 503, 'Platform login onboarding did not issue an invitation. No Workspace invitation was created.');
        $plainToken = Str::random(64);
        $expiresAt = now()->addHours(72);
        if ($identityInvitation['expires_at'] !== null) $expiresAt = min($expiresAt, \Illuminate\Support\Carbon::parse($identityInvitation['expires_at']));
        $id = (string) Str::uuid();
        try {
            DB::table('platform_workspace_invitations')->insert([
                'id' => $id, 'workspace_id' => $workspaceId, 'email' => $email, 'role' => $role,
                'token_hash' => hash('sha256', $plainToken), 'identity_invitation_token' => Crypt::encryptString($identityInvitationToken),
                'created_by' => $accountId, 'expires_at' => $expiresAt, 'created_at' => now(), 'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            if ($identityInvitationToken !== '') {
                try { $this->identity->revokeLoginInvitation($identityInvitationToken); }
                catch (RuntimeException) { /* Preserve the persistence error; no Workspace invite was committed. */ }
            }
            throw $exception;
        }
        return [
            'id' => $id, 'workspace_id' => $workspaceId, 'email' => $email, 'role' => $role, 'invite_token' => $plainToken,
            'identity_invitation_token' => $identityInvitationToken !== '' ? $identityInvitationToken : null,
            'identity_already_a_member' => $identityInvitation['already_a_member'], 'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /** @return array{invitations: list<array<string, mixed>>} */
    public function workspaceInvitations(string $workspaceId, string $accountId): array
    {
        $this->requireWorkspaceAdmin($workspaceId, $accountId);
        $invitations = DB::table('platform_workspace_invitations')->where('workspace_id', $workspaceId)->latest()->get();
        return ['invitations' => $invitations->map(static fn ($invitation): array => [
            'id' => (string) $invitation->id, 'workspace_id' => (string) $invitation->workspace_id,
            'email' => (string) $invitation->email, 'role' => (string) $invitation->role,
            'expires_at' => (string) $invitation->expires_at, 'revoked_at' => $invitation->revoked_at, 'accepted_at' => $invitation->accepted_at,
            'status' => $invitation->accepted_at !== null ? 'accepted' : ($invitation->revoked_at !== null ? 'revoked' : (now()->greaterThan($invitation->expires_at) ? 'expired' : 'pending')),
        ])->all()];
    }

    /** @return array<string, mixed> */
    public function revokeWorkspaceInvitation(string $workspaceId, string $invitationId, string $accountId): array
    {
        $this->requireWorkspaceAdmin($workspaceId, $accountId);
        $invitation = DB::table('platform_workspace_invitations')->where('workspace_id', $workspaceId)->where('id', $invitationId)->first();
        abort_if($invitation === null, 404, 'Invitation not found.');
        abort_if($invitation->accepted_at !== null, 409, 'An accepted invitation cannot be revoked.');
        $identityToken = '';
        try { $identityToken = Crypt::decryptString((string) $invitation->identity_invitation_token); }
        catch (DecryptException|RuntimeException) { /* Older or already-cleared invitations have no linked Identity token. */ }
        DB::table('platform_workspace_invitations')->where('id', $invitationId)->update([
            'revoked_at' => $invitation->revoked_at ?? now(), 'identity_invitation_token' => Crypt::encryptString(''), 'updated_at' => now(),
        ]);
        $identityLoginInvitationRevoked = true;
        if ($identityToken !== '') {
            try { $this->identity->revokeLoginInvitation($identityToken); }
            catch (RuntimeException) { $identityLoginInvitationRevoked = false; }
        }
        return ['id' => $invitationId, 'status' => 'revoked', 'identity_login_invitation_revoked' => $identityLoginInvitationRevoked];
    }

    /** @return array{workspace: array<string, mixed>, already_a_member: bool} */
    public function acceptWorkspaceInvitation(string $token, string $accessToken): array
    {
        try { $identity = $this->identity->currentIdentity($accessToken); }
        catch (IdentityApiAccessDenied) { abort(401, 'Sign in through the Platform Identity project to accept this invitation.'); }
        catch (IdentityApiUnavailable) { abort(503, 'Platform Identity is temporarily unavailable. The Workspace invitation was not accepted.'); }
        catch (RuntimeException) { abort(401, 'Sign in through the Platform Identity project to accept this invitation.'); }
        abort_unless($identity['project_slug'] === (string) config('services.platform_onboarding.login_project_slug'), 403, 'Sign in through the Platform Identity project to accept this invitation.');
        $result = DB::transaction(function () use ($token, $identity): array {
            $invitation = DB::table('platform_workspace_invitations')->where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            abort_if($invitation === null || $invitation->revoked_at !== null || $invitation->accepted_at !== null || now()->greaterThan($invitation->expires_at), 404, 'This invitation is invalid or expired.');
            abort_unless(hash_equals((string) $invitation->email, mb_strtolower(trim($identity['email']))), 403, 'Use the invited email address to accept this invitation.');
            $accountId = (string) $identity['user_id'];
            abort_if($accountId === '', 401, 'The signed-in Identity account is unavailable.');
            $workspace = DB::table('platform_workspaces')->where('id', $invitation->workspace_id)->whereNull('archived_at')->first();
            abort_if($workspace === null, 404, 'This Workspace is unavailable.');
            $membership = DB::table('platform_workspace_memberships')->where('workspace_id', $invitation->workspace_id)->where('account_id', $accountId)->lockForUpdate()->first();
            $alreadyMember = $membership !== null && $membership->status === 'active';
            $now = now();
            if ($membership === null) {
                DB::table('platform_workspace_memberships')->insert([
                    'id' => (string) Str::uuid(), 'workspace_id' => $invitation->workspace_id, 'account_id' => $accountId,
                    'role' => $invitation->role, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
                ]);
            } elseif ($membership->status !== 'active') {
                DB::table('platform_workspace_memberships')->where('id', $membership->id)->update(['role' => $invitation->role, 'status' => 'active', 'updated_at' => $now]);
            } elseif ($membership->role !== 'owner' && $invitation->role === 'admin' && $membership->role !== 'admin') {
                DB::table('platform_workspace_memberships')->where('id', $membership->id)->update(['role' => 'admin', 'updated_at' => $now]);
            }
            DB::table('platform_workspace_invitations')->where('id', $invitation->id)->update([
                'accepted_at' => $now, 'identity_invitation_token' => Crypt::encryptString(''), 'updated_at' => $now,
            ]);
            return ['workspace_id' => (string) $invitation->workspace_id, 'already_a_member' => $alreadyMember];
        });
        return [
            'workspace' => $this->workspaceRole($result['workspace_id'], (string) $identity['user_id']),
            'already_a_member' => $result['already_a_member'],
        ];
    }

    private function requireWorkspaceAdmin(string $workspaceId, string $accountId): array
    {
        $workspace = $this->workspaceRole($workspaceId, $accountId);
        abort_unless(OrganizationAccessPolicy::canAdministerWorkspace($workspace['role']), 403, 'Workspace administration is required.');
        return $workspace;
    }

    /** @return array<string, mixed> */
    private function workspaceRole(string $workspaceId, string $accountId): array
    {
        $row = DB::table('platform_workspaces as workspace')
            ->join('platform_workspace_memberships as membership', function ($join) use ($accountId): void {
                $join->on('membership.workspace_id', '=', 'workspace.id')->where('membership.account_id', '=', $accountId)->where('membership.status', '=', 'active');
            })
            ->leftJoin('platform_account_workspace_defaults as defaults', function ($join) use ($accountId): void {
                $join->on('defaults.workspace_id', '=', 'workspace.id')->where('defaults.account_id', '=', $accountId);
            })
            ->where('workspace.id', $workspaceId)->whereNull('workspace.archived_at')
            ->first(['workspace.id', 'workspace.owner_id', 'workspace.name', 'workspace.slug', 'workspace.status', 'membership.role', 'defaults.workspace_id as default_workspace_id']);
        abort_if($row === null, 404, 'Workspace not found.');
        return [
            'id' => (string) $row->id, 'name' => (string) $row->name, 'slug' => (string) $row->slug, 'status' => (string) $row->status,
            'role' => (string) $row->role, 'owner_account_id' => (string) $row->owner_id,
            'member_count' => (int) DB::table('platform_workspace_memberships')->where('workspace_id', $workspaceId)->where('status', 'active')->count(),
            'is_default' => $row->default_workspace_id !== null,
        ];
    }

}
