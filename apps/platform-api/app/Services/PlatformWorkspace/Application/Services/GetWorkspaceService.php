<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Services;

use App\Services\PlatformWorkspace\Application\DTOs\Input\GetWorkspaceDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Output\WorkspaceResponseDTO;
use App\Services\PlatformWorkspace\Application\Queries\GetWorkspaceQuery;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetWorkspaceService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetWorkspaceDTO $input): WorkspaceResponseDTO
    {
        ['workspace' => $workspace] = $this->applicationService->runAndCapture(GetWorkspaceQuery::class, [
            'workspaceId' => WorkspaceId::fromString($input->workspaceId),
            'accountId' => AccountId::fromString($input->accountId),
        ])->getOrFail();

        return WorkspaceResponseDTO::fromView($workspace);
    }
}
