<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Services;

use App\Services\PlatformWorkspace\Application\DTOs\Input\ListWorkspacesDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Output\WorkspaceCollectionResponseDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Output\WorkspaceResponseDTO;
use App\Services\PlatformWorkspace\Application\Queries\ListWorkspacesQuery;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ListWorkspacesService
{
    public function __construct(private ApplicationService $applicationService) {}
    public function __invoke(ListWorkspacesDTO $input): WorkspaceCollectionResponseDTO
    {
        ['workspaces' => $workspaces] = $this->applicationService->runAndCapture(ListWorkspacesQuery::class, ['accountId' => AccountId::fromString($input->accountId)])->getOrFail();
        return new WorkspaceCollectionResponseDTO(array_map(static fn ($workspace): WorkspaceResponseDTO => WorkspaceResponseDTO::fromView($workspace), $workspaces));
    }
}
