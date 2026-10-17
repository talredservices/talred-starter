<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\API\Controllers;

use App\Services\PlatformOrganization\Application\Contracts\PlatformOrganizationManagement;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;
use App\Services\PlatformWorkspace\Application\Services\WorkspaceIdentityProjectService;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceNotFound;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourceAlreadyLinked;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourceNotFound;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourcePermissionDenied;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Zolta\Http\Authorization\Identity;

final class PlatformOrganizationController
{
    public function __construct(
        private readonly PlatformOrganizationManagement $organization,
        private readonly WorkspaceIdentityProjectService $identityProjects,
    ) {}

    public function workspaceOverview(Request $request, string $workspaceId): JsonResponse
    {
        return $this->respondResource(function () use ($request, $workspaceId): array {
            $accountId = $this->accountId();
            $workspace = $this->organization->getWorkspace($workspaceId, $accountId)['workspace'];
            $identityProjects = $this->identityProjects->workspaceIdentityProjects($workspaceId, $accountId, $this->bearerToken($request));

            return ['workspace' => $workspace, 'resources' => ['identity_projects' => $identityProjects]];
        });
    }

    public function workspace(string $workspaceId): JsonResponse
    {
        return $this->respond($this->organization->getWorkspace($workspaceId, $this->accountId()));
    }

    public function updateWorkspace(Request $request, string $workspaceId): JsonResponse
    {
        $input = $request->validate(['name' => ['required', 'string', 'max:120']]);
        return $this->respond($this->organization->renameWorkspace($workspaceId, $this->accountId(), $input['name']));
    }

    public function workspaceMembers(Request $request, string $workspaceId): JsonResponse
    {
        return $this->respond($this->organization->workspaceMembers($workspaceId, $this->accountId()));
    }

    public function updateWorkspaceMember(Request $request, string $workspaceId, string $accountId): JsonResponse
    {
        $input = $request->validate(['role' => ['required', 'in:admin,member']]);
        return $this->respond($this->organization->updateWorkspaceMember($workspaceId, $this->accountId(), $accountId, $input['role']));
    }

    public function removeWorkspaceMember(Request $request, string $workspaceId, string $accountId): JsonResponse
    {
        return $this->respond($this->organization->removeWorkspaceMember($workspaceId, $this->accountId(), $accountId));
    }

    public function transferWorkspaceOwnership(Request $request, string $workspaceId): JsonResponse
    {
        $input = $request->validate(['account_id' => ['required', 'uuid']]);
        return $this->respond($this->organization->transferWorkspaceOwnership($workspaceId, $this->accountId(), $input['account_id']));
    }

    public function workspaceInvitations(Request $request, string $workspaceId): JsonResponse
    {
        return $this->respond($this->organization->workspaceInvitations($workspaceId, $this->accountId()));
    }

    public function createWorkspaceInvitation(Request $request, string $workspaceId): JsonResponse
    {
        $input = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'role' => ['required', 'in:admin,member'],
        ]);
        return $this->respond($this->organization->createWorkspaceInvitation($workspaceId, $this->accountId(), $input['email'], $input['role']), 201);
    }

    public function revokeWorkspaceInvitation(Request $request, string $workspaceId, string $invitationId): JsonResponse
    {
        return $this->respond($this->organization->revokeWorkspaceInvitation($workspaceId, $invitationId, $this->accountId()));
    }

    public function acceptWorkspaceInvitation(Request $request): JsonResponse
    {
        $input = $request->validate(['token' => ['required', 'string', 'min:32', 'max:128']]);
        return $this->respond($this->organization->acceptWorkspaceInvitation($input['token'], $this->bearerToken($request)));
    }

    public function workspaceIdentityProjects(Request $request, string $workspaceId): JsonResponse
    {
        return $this->respondResource(fn (): array => $this->identityProjects->workspaceIdentityProjects($workspaceId, $this->accountId(), $this->bearerToken($request)));
    }

    public function availableIdentityProjects(Request $request, string $workspaceId): JsonResponse
    {
        return $this->respondResource(fn (): array => $this->identityProjects->availableIdentityProjects($workspaceId, $this->accountId(), $this->bearerToken($request)));
    }

    public function linkIdentityProject(Request $request, string $workspaceId): JsonResponse
    {
        $input = $request->validate(['identity_project_id' => ['required', 'uuid']]);
        return $this->respondResource(fn (): array => $this->identityProjects->linkIdentityProjectToWorkspace($workspaceId, $input['identity_project_id'], $this->accountId(), $this->bearerToken($request)), 201);
    }

    public function unlinkIdentityProject(Request $request, string $workspaceId, string $identityProjectId): JsonResponse
    {
        return $this->respondResource(fn (): array => $this->identityProjects->unlinkIdentityProjectFromWorkspace($workspaceId, $identityProjectId, $this->accountId()));
    }

    private function accountId(): string
    {
        $id = (string) Identity::current()?->getId();
        abort_if($id === '', 401, 'Authentication is required.');
        return $id;
    }

    private function bearerToken(Request $request): string
    {
        $token = (string) $request->bearerToken();
        abort_if($token === '', 401, 'An Identity access token is required for this operation.');
        return $token;
    }

    /** @param callable(): array<string, mixed> $operation */
    private function respondResource(callable $operation, int $status = 200): JsonResponse
    {
        try {
            return $this->respond($operation(), $status);
        } catch (WorkspaceNotFound|WorkspaceResourceNotFound $exception) {
            abort(404, $exception->getMessage());
        } catch (WorkspaceResourcePermissionDenied|IdentityApiAccessDenied $exception) {
            abort(403, $exception->getMessage());
        } catch (WorkspaceResourceAlreadyLinked $exception) {
            abort(409, $exception->getMessage());
        } catch (IdentityApiUnavailable $exception) {
            abort(503, $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }
    }

    /** @param array<string, mixed> $data */
    private function respond(array $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }
}
