<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Services;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;
use App\Services\PlatformWorkspace\Domain\Entities\WorkspaceResourceLink;
use App\Services\PlatformWorkspace\Domain\Enums\WorkspaceResourceType;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceNotFound;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourceAlreadyLinked;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourceNotFound;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourcePermissionDenied;
use App\Services\PlatformWorkspace\Domain\Policies\WorkspaceResourceAccessPolicy;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceRepository;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceResourceRepository;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceResourceLinkId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceResourceReference;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

final readonly class WorkspaceIdentityProjectService
{
    public function __construct(
        private WorkspaceRepository $workspaces,
        private WorkspaceResourceRepository $resources,
        private IdentityApiGateway $identity,
    ) {}

    /** @return array{availability: string, projects: list<array<string, mixed>>} */
    public function identityProjectDirectory(string $accessToken): array
    {
        $identityProjects = $this->identityProjectSummaries($accessToken);

        return [
            'availability' => $identityProjects['availability'],
            'projects' => array_values($identityProjects['projects']),
        ];
    }

    /** @return array{availability: string, projects: list<array<string, mixed>>} */
    public function workspaceIdentityProjects(string $workspaceId, string $accountId, string $accessToken): array
    {
        [$workspaceIdObject] = $this->accessibleWorkspace($workspaceId, $accountId);
        $identityProjectIds = $this->resources->externalIdsForWorkspace($workspaceIdObject, WorkspaceResourceType::IdentityProject);

        if ($identityProjectIds === []) {
            return ['availability' => 'available', 'projects' => []];
        }

        $identityProjects = $this->identityProjectSummaries($accessToken, $identityProjectIds);
        $projects = array_map(function (string $id) use ($identityProjects): array {
            return $identityProjects['projects'][$id] ?? [
                'id' => $id,
                'availability' => $identityProjects['availability'] === 'available' ? 'access_needed' : $identityProjects['availability'],
            ];
        }, $identityProjectIds);

        return ['availability' => $identityProjects['availability'], 'projects' => $projects];
    }

    /** @return array{availability: string, projects: list<array<string, mixed>>} */
    public function availableIdentityProjects(string $workspaceId, string $accountId, string $accessToken): array
    {
        $this->accessibleWorkspace($workspaceId, $accountId);
        $identityProjects = $this->identityProjectSummaries($accessToken);
        if ($identityProjects['availability'] !== 'available') {
            return ['availability' => $identityProjects['availability'], 'projects' => []];
        }

        $linkedIds = $this->resources->allExternalIds(WorkspaceResourceType::IdentityProject);

        return [
            'availability' => 'available',
            'projects' => array_values(array_filter(
                $identityProjects['projects'],
                static fn (array $project): bool => ! in_array($project['id'], $linkedIds, true),
            )),
        ];
    }

    /** @return array{resource: array<string, string>} */
    public function linkIdentityProjectToWorkspace(string $workspaceId, string $identityProjectId, string $accountId, string $accessToken): array
    {
        [$workspaceIdObject, $accountIdObject, $workspace] = $this->accessibleWorkspace($workspaceId, $accountId, requireAdmin: true);
        try {
            $resource = WorkspaceResourceReference::identityProject($identityProjectId);
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException('Identity Project id is invalid.');
        }

        $identityProjects = $this->identityProjectSummaries($accessToken);
        if ($identityProjects['availability'] === 'access_needed') {
            throw new IdentityApiAccessDenied('Identity project access is required to link this resource.');
        }
        if ($identityProjects['availability'] === 'unavailable') {
            throw new IdentityApiUnavailable('Identity projects are temporarily unavailable. No link was created.');
        }
        if (! isset($identityProjects['projects'][$resource->externalId])) {
            throw new WorkspaceResourceNotFound();
        }
        if ($this->resources->isLinked($resource)) {
            throw new WorkspaceResourceAlreadyLinked();
        }

        $link = new WorkspaceResourceLink(
            WorkspaceResourceLinkId::default(),
            $workspaceIdObject->toString(),
            $resource,
            $accountIdObject->toString(),
            new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );
        $this->resources->save($link);

        return ['resource' => [
            'id' => $link->id->toString(),
            'workspace_id' => $link->workspaceId,
            'type' => $resource->type->value,
            'external_resource_id' => $resource->externalId,
        ]];
    }

    /** @return array{workspace_id: string, identity_project_id: string, linked: false} */
    public function unlinkIdentityProjectFromWorkspace(string $workspaceId, string $identityProjectId, string $accountId): array
    {
        [$workspaceIdObject] = $this->accessibleWorkspace($workspaceId, $accountId, requireAdmin: true);
        try {
            $resource = WorkspaceResourceReference::identityProject($identityProjectId);
        } catch (InvalidArgumentException) {
            throw new WorkspaceResourceNotFound();
        }

        if (! $this->resources->unlink($workspaceIdObject, $resource)) {
            throw new WorkspaceResourceNotFound();
        }

        return ['workspace_id' => $workspaceIdObject->toString(), 'identity_project_id' => $resource->externalId, 'linked' => false];
    }

    /** @return array{0: WorkspaceId, 1: AccountId, 2: \App\Services\PlatformWorkspace\Application\Projections\WorkspaceView} */
    private function accessibleWorkspace(string $workspaceId, string $accountId, bool $requireAdmin = false): array
    {
        try {
            $workspaceIdObject = WorkspaceId::fromString($workspaceId);
            $accountIdObject = AccountId::fromString($accountId);
        } catch (InvalidArgumentException) {
            throw new WorkspaceNotFound();
        }

        $workspace = $this->workspaces->findAccessible($workspaceIdObject, $accountIdObject);
        if ($workspace === null) {
            throw new WorkspaceNotFound();
        }
        if ($requireAdmin && ! WorkspaceResourceAccessPolicy::canManage($workspace->role)) {
            throw new WorkspaceResourcePermissionDenied();
        }

        return [$workspaceIdObject, $accountIdObject, $workspace];
    }

    /** @param list<string> $detailsFor @return array{availability: string, projects: array<string, array<string, mixed>>} */
    private function identityProjectSummaries(string $accessToken, array $detailsFor = []): array
    {
        try {
            $projects = $this->identity->listProjects($accessToken);
        } catch (IdentityApiAccessDenied) {
            return ['availability' => 'access_needed', 'projects' => []];
        } catch (RuntimeException) {
            return ['availability' => 'unavailable', 'projects' => []];
        }

        $projectMap = [];
        foreach ($projects as $project) {
            $id = (string) ($project['id'] ?? '');
            if ($id !== '') {
                $projectMap[$id] = [
                    'id' => $id,
                    'name' => (string) ($project['name'] ?? 'Identity Project'),
                    'slug' => (string) ($project['slug'] ?? ''),
                    'status' => (string) ($project['status'] ?? 'unknown'),
                    'availability' => 'available',
                ];
            }
        }

        foreach (array_unique($detailsFor) as $id) {
            $id = (string) $id;
            if (! isset($projectMap[$id])) {
                continue;
            }
            try {
                $details = $this->identity->projectDetails($accessToken, $id);
            } catch (IdentityApiAccessDenied) {
                $details = ['status' => 'access_needed'];
            } catch (RuntimeException) {
                $details = ['status' => 'unavailable'];
            }
            if ($details['status'] === 'available') {
                $projectMap[$id] = $this->sanitizeIdentityProject($details['project'] ?? [], $projectMap[$id]);
            } else {
                $projectMap[$id] = ['id' => $id, 'availability' => $details['status']];
            }
        }

        return ['availability' => 'available', 'projects' => $projectMap];
    }

    /** @param array<string, mixed> $details @param array<string, mixed> $summary @return array<string, mixed> */
    private function sanitizeIdentityProject(array $details, array $summary): array
    {
        $project = (array) ($details['project'] ?? $details);
        $clients = $details['clients'] ?? $project['clients'] ?? $details['confidential_clients'] ?? $project['confidential_clients'] ?? [];
        $applications = $details['hosted_applications'] ?? $project['hosted_applications'] ?? $details['applications'] ?? $project['applications'] ?? [];

        return $summary + [
            'availability' => 'available',
            'clients' => is_array($clients) ? array_map(static fn ($client): array => [
                'id' => (string) ($client['id'] ?? ''),
                'name' => (string) ($client['name'] ?? 'Client'),
                'secret_prefix' => isset($client['secret_prefix']) ? (string) $client['secret_prefix'] : null,
                'status' => (string) ($client['status'] ?? 'unknown'),
            ], array_filter($clients, 'is_array')) : [],
            'applications' => is_array($applications) ? array_map(static fn ($application): array => [
                'id' => (string) ($application['id'] ?? ''),
                'name' => (string) ($application['name'] ?? 'Application'),
                'key' => (string) ($application['key'] ?? ''),
                'status' => (string) ($application['status'] ?? 'unknown'),
                'application_url' => isset($application['application_url']) ? (string) $application['application_url'] : null,
            ], array_filter($applications, 'is_array')) : [],
            'identity_project' => [
                'id' => (string) ($project['id'] ?? $summary['id']),
                'name' => (string) ($project['name'] ?? $summary['name']),
                'slug' => (string) ($project['slug'] ?? $summary['slug']),
                'status' => (string) ($project['status'] ?? $summary['status']),
            ],
        ];
    }
}
