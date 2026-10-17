<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Services;

use App\Services\PlatformWorkspace\Application\DTOs\Input\CreateWorkspaceDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Input\EnsureDefaultWorkspaceDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Input\GetWorkspaceDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Input\ListWorkspacesDTO;
use App\Services\PlatformWorkspace\Application\Services\CreateWorkspaceService;
use App\Services\PlatformWorkspace\Application\Services\EnsureDefaultWorkspaceService;
use App\Services\PlatformWorkspace\Application\Services\GetWorkspaceService;
use App\Services\PlatformWorkspace\Application\Services\ListWorkspacesService;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceNotFound;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceSlugAlreadyExists;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final readonly class WorkspaceHttpService
{
    public function __construct(
        private CreateWorkspaceService $create,
        private EnsureDefaultWorkspaceService $ensureDefault,
        private GetWorkspaceService $get,
        private ListWorkspacesService $list,
    ) {}

    public function __invoke(CreateWorkspaceDTO|EnsureDefaultWorkspaceDTO|GetWorkspaceDTO|ListWorkspacesDTO $input): ResponseDTO
    {
        try {
            return match (true) {
                $input instanceof CreateWorkspaceDTO => ($this->create)($input),
                $input instanceof EnsureDefaultWorkspaceDTO => ($this->ensureDefault)($input),
                $input instanceof GetWorkspaceDTO => ($this->get)($input),
                $input instanceof ListWorkspacesDTO => ($this->list)($input),
            };
        } catch (WorkspaceNotFound $exception) {
            throw new HttpException(404, $exception->getMessage(), $exception);
        } catch (WorkspaceSlugAlreadyExists $exception) {
            throw new HttpException(409, $exception->getMessage(), $exception);
        } catch (InvalidArgumentException $exception) {
            throw new HttpException(422, $exception->getMessage(), $exception);
        }
    }
}
