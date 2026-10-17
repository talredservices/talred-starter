<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\Infrastructure\Services;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class HttpIdentityApiGateway implements IdentityApiGateway
{
    public function issueLoginInvitation(string $email): array
    {
        try {
            $response = Http::baseUrl(rtrim((string) config('services.platform_onboarding.identity_api_url'), '/'))
                ->acceptJson()
                ->timeout(8)
                ->post('/api/v1/identity/platform/onboarding/invitations', [
                    'client_id' => config('services.platform_onboarding.identity_client_id'),
                    'client_secret' => config('services.platform_onboarding.identity_client_secret'),
                    'email' => $email,
                ]);
        } catch (ConnectionException $exception) {
            throw new IdentityApiUnavailable('Platform login invitation could not be issued.', previous: $exception);
        }

        if (! $response->successful()) {
            throw new IdentityApiUnavailable('Platform login invitation could not be issued.');
        }

        $data = $this->responseData($response);

        return [
            'already_a_member' => (bool) ($data['already_a_member'] ?? false),
            'invitation_token' => isset($data['invitation_token']) ? (string) $data['invitation_token'] : null,
            'expires_at' => isset($data['expires_at']) ? (string) $data['expires_at'] : null,
        ];
    }

    public function currentIdentity(string $accessToken): array
    {
        try {
            $response = $this->userRequest($accessToken)->get('/api/v1/identity/auth/me');
        } catch (ConnectionException $exception) {
            throw new IdentityApiUnavailable('The signed-in Identity context could not be confirmed.', previous: $exception);
        }
        if (! $response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new IdentityApiAccessDenied('The signed-in Identity context could not be confirmed.');
            }

            throw new IdentityApiUnavailable('The signed-in Identity context could not be confirmed.');
        }

        $data = $this->responseData($response);
        $email = (string) data_get($data, 'user.email', data_get($data, 'email', ''));
        $projectSlug = (string) data_get($data, 'project.slug', data_get($data, 'project_slug', ''));

        if ($email === '' || $projectSlug === '') {
            throw new RuntimeException('The signed-in Identity context is incomplete.');
        }

        $userId = (string) data_get($data, 'user.id', data_get($data, 'id', ''));
        if ($userId === '') {
            throw new RuntimeException('The signed-in Identity account is incomplete.');
        }

        return ['user_id' => $userId, 'email' => mb_strtolower(trim($email)), 'project_slug' => $projectSlug];
    }

    public function listProjects(string $accessToken): array
    {
        try {
            $response = $this->userRequest($accessToken)->get('/api/v1/identity/projects');
        } catch (ConnectionException $exception) {
            throw new IdentityApiUnavailable('Identity projects could not be retrieved.', previous: $exception);
        }
        if (! $response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new IdentityApiAccessDenied('Identity projects could not be retrieved.');
            }

            throw new IdentityApiUnavailable('Identity projects could not be retrieved.');
        }

        $data = $this->responseData($response);
        $projects = $data['projects'] ?? $data['items'] ?? $data;

        return is_array($projects) ? array_values(array_filter($projects, 'is_array')) : [];
    }

    public function listApplications(string $accessToken): array
    {
        try {
            $response = $this->userRequest($accessToken)->get('/api/v1/identity/applications');
        } catch (ConnectionException $exception) {
            throw new IdentityApiUnavailable('Identity applications could not be retrieved.', previous: $exception);
        }
        if (! $response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new IdentityApiAccessDenied('Identity applications could not be retrieved.');
            }

            throw new IdentityApiUnavailable('Identity applications could not be retrieved.');
        }

        $data = $this->responseData($response);
        $applications = $data['applications'] ?? $data['items'] ?? $data;

        return is_array($applications) ? array_values(array_filter($applications, 'is_array')) : [];
    }

    public function projectDetails(string $accessToken, string $identityProjectId): array
    {
        try {
            $response = $this->userRequest($accessToken)->get('/api/v1/identity/projects/'.$identityProjectId);
        } catch (ConnectionException) {
            return ['status' => 'unavailable'];
        }

        if ($response->status() === 403 || $response->status() === 404) {
            return ['status' => 'access_needed'];
        }

        if (! $response->successful()) {
            return ['status' => 'unavailable'];
        }

        $data = $this->responseData($response);

        return ['status' => 'available', 'project' => (array) ($data['project'] ?? $data)];
    }

    public function revokeLoginInvitation(string $token): void
    {
        try {
            $response = Http::baseUrl(rtrim((string) config('services.platform_onboarding.identity_api_url'), '/'))
                ->acceptJson()
                ->timeout(8)
                ->post('/api/v1/identity/platform/onboarding/invitations/revoke', [
                    'client_id' => config('services.platform_onboarding.identity_client_id'),
                    'client_secret' => config('services.platform_onboarding.identity_client_secret'),
                    'invitation_token' => $token,
                ]);
        } catch (ConnectionException $exception) {
            throw new IdentityApiUnavailable('The linked platform login invitation could not be revoked.', previous: $exception);
        }

        if (! $response->successful()) {
            throw new IdentityApiUnavailable('The linked platform login invitation could not be revoked.');
        }
    }

    private function userRequest(string $accessToken): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.platform_onboarding.identity_api_url'), '/'))
            ->withToken($accessToken)
            ->acceptJson()
            ->timeout(8);
    }

    /** @return array<string, mixed> */
    private function responseData(Response $response): array
    {
        $data = $response->json();

        for ($i = 0; $i < 3 && is_array($data); $i++) {
            if (isset($data['data']) && is_array($data['data'])) {
                $data = $data['data'];

                continue;
            }
            if (isset($data['response']) && is_array($data['response'])) {
                $data = $data['response'];

                continue;
            }
            if (isset($data['result']) && is_array($data['result'])) {
                $data = $data['result'];

                continue;
            }
            break;
        }

        return is_array($data) ? $data : [];
    }
}
