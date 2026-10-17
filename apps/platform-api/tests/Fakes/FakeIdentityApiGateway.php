<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;
use RuntimeException;

final class FakeIdentityApiGateway implements IdentityApiGateway
{
    public bool $failInvitationIssue = false;

    public ?string $projectListFailure = null;

    /** @var array{user_id: string, email: string, project_slug: string} */
    public array $identity = ['user_id' => '', 'email' => '', 'project_slug' => 'talred-platform'];

    /** @var list<array<string, mixed>> */
    public array $identityProjects = [];

    /** @var list<array<string, mixed>> */
    public array $identityApplications = [];

    /** @var array<string, array<string, mixed>> */
    public array $projectDetails = [];

    /** @var array<string, string> */
    public array $projectDetailFailures = [];

    /** @var list<string> */
    public array $revokedInvitations = [];

    public function issueLoginInvitation(string $email): array
    {
        if ($this->failInvitationIssue) {
            throw new RuntimeException('Identity onboarding failed.');
        }

        return [
            'already_a_member' => false,
            'invitation_token' => str_repeat('identity-token-', 4),
            'expires_at' => now()->addHours(72)->toIso8601String(),
        ];
    }

    public function currentIdentity(string $accessToken): array
    {
        return $this->identity;
    }

    public function listProjects(string $accessToken): array
    {
        if ($this->projectListFailure === 'access_denied') {
            throw new IdentityApiAccessDenied('Identity access denied.');
        }
        if ($this->projectListFailure === 'unavailable') {
            throw new IdentityApiUnavailable('Identity is unavailable.');
        }

        return $this->identityProjects;
    }

    public function listApplications(string $accessToken): array
    {
        return $this->identityApplications;
    }

    public function projectDetails(string $accessToken, string $identityProjectId): array
    {
        if (($this->projectDetailFailures[$identityProjectId] ?? null) === 'access_denied') {
            throw new IdentityApiAccessDenied('Identity project access denied.');
        }
        if (($this->projectDetailFailures[$identityProjectId] ?? null) === 'unavailable') {
            throw new IdentityApiUnavailable('Identity project is unavailable.');
        }

        return $this->projectDetails[$identityProjectId] ?? ['status' => 'access_needed'];
    }

    public function revokeLoginInvitation(string $token): void
    {
        $this->revokedInvitations[] = $token;
    }
}
