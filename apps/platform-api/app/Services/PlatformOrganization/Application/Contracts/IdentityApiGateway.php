<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\Application\Contracts;

interface IdentityApiGateway
{
    /** @return array{already_a_member: bool, invitation_token: ?string, expires_at: ?string} */
    public function issueLoginInvitation(string $email): array;

    /** @return array{user_id: string, email: string, project_slug: string} */
    public function currentIdentity(string $accessToken): array;

    /** @return list<array<string, mixed>> */
    public function listProjects(string $accessToken): array;

    /** @return list<array<string, mixed>> */
    public function listApplications(string $accessToken): array;

    /** @return array{status: 'available'|'access_needed'|'unavailable', project?: array<string, mixed>} */
    public function projectDetails(string $accessToken, string $identityProjectId): array;

    public function revokeLoginInvitation(string $token): void;
}
