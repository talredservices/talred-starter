<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Services;

use App\Services\PlatformWorkspace\Application\DTOs\Input\EnsureDefaultWorkspaceDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Output\WorkspaceResponseDTO;
use App\Services\PlatformWorkspace\Application\Commands\EnsureDefaultWorkspaceCommand;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class EnsureDefaultWorkspaceService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(EnsureDefaultWorkspaceDTO $input): WorkspaceResponseDTO
    {
        return $this->applicationService->transactional(function () use ($input): WorkspaceResponseDTO {
            ['workspace' => $workspace] = $this->applicationService->runAndCapture(EnsureDefaultWorkspaceCommand::class, [
                'accountId' => AccountId::fromString($input->accountId),
            ])->getOrFail();

            return WorkspaceResponseDTO::fromView($workspace);
        });
    }
}
