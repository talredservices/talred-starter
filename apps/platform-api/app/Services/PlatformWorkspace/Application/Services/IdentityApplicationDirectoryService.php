<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Services;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;

final readonly class IdentityApplicationDirectoryService
{
    public function __construct(private IdentityApiGateway $identity) {}

    /** @return array{availability: string, applications: list<array<string, mixed>>} */
    public function list(string $accessToken): array
    {
        try {
            $applications = $this->identity->listApplications($accessToken);
        } catch (IdentityApiAccessDenied) {
            return ['availability' => 'access_needed', 'applications' => []];
        } catch (IdentityApiUnavailable) {
            return ['availability' => 'unavailable', 'applications' => []];
        }

        return [
            'availability' => 'available',
            'applications' => array_values(array_filter(array_map(
                static function (array $application): ?array {
                    $id = (string) ($application['id'] ?? '');
                    $project = $application['project'] ?? null;
                    $projectId = is_array($project) ? (string) ($project['id'] ?? '') : '';
                    if ($id === '' || $projectId === '') {
                        return null;
                    }

                    $applicationUrl = isset($application['application_url']) ? (string) $application['application_url'] : null;
                    $urlParts = $applicationUrl === null ? false : parse_url($applicationUrl);
                    $safeApplicationUrl = is_array($urlParts)
                        && in_array(strtolower((string) ($urlParts['scheme'] ?? '')), ['http', 'https'], true)
                        && ! empty($urlParts['host'])
                        && ! isset($urlParts['user'])
                        && ! isset($urlParts['pass'])
                        && ! isset($urlParts['query'])
                        && ! isset($urlParts['fragment'])
                        ? $applicationUrl
                        : null;

                    return [
                        'id' => $id,
                        'name' => (string) ($application['name'] ?? 'Application'),
                        'key' => (string) ($application['key'] ?? ''),
                        'status' => (string) ($application['status'] ?? 'unknown'),
                        'application_url' => $safeApplicationUrl,
                        'project' => [
                            'id' => $projectId,
                            'name' => (string) ($project['name'] ?? 'Identity Project'),
                            'slug' => (string) ($project['slug'] ?? ''),
                            'status' => (string) ($project['status'] ?? 'unknown'),
                            'mode' => (string) ($project['mode'] ?? 'unknown'),
                        ],
                    ];
                },
                $applications,
            ), 'is_array')),
        ];
    }
}
