<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\Application\Contracts;

interface PlatformOrganizationManagement
{
    /** @return array{workspace: array<string, mixed>} */
    public function getWorkspace(string $workspaceId, string $accountId): array;

    /** @return array{workspace: array<string, mixed>} */
    public function renameWorkspace(string $workspaceId, string $accountId, string $name): array;

    /** @return array{members: list<array<string, mixed>>} */
    public function workspaceMembers(string $workspaceId, string $accountId): array;

    /** @return array{account_id: string, role: string} */
    public function updateWorkspaceMember(string $workspaceId, string $accountId, string $targetAccountId, string $role): array;

    /** @return array{account_id: string, status: string} */
    public function removeWorkspaceMember(string $workspaceId, string $accountId, string $targetAccountId): array;

    /** @return array{workspace_id: string, owner_account_id: string} */
    public function transferWorkspaceOwnership(string $workspaceId, string $accountId, string $targetAccountId): array;

    /** @return array<string, mixed> */
    public function createWorkspaceInvitation(string $workspaceId, string $accountId, string $email, string $role): array;

    /** @return array{invitations: list<array<string, mixed>>} */
    public function workspaceInvitations(string $workspaceId, string $accountId): array;

    /** @return array<string, mixed> */
    public function revokeWorkspaceInvitation(string $workspaceId, string $invitationId, string $accountId): array;

    /** @return array{workspace: array<string, mixed>, already_a_member: bool} */
    public function acceptWorkspaceInvitation(string $token, string $accessToken): array;

}
