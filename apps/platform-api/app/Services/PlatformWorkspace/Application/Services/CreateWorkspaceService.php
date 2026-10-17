<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Services;

use App\Services\PlatformWorkspace\Application\Commands\CreateWorkspaceCommand;
use App\Services\PlatformWorkspace\Application\DTOs\Input\CreateWorkspaceDTO;
use App\Services\PlatformWorkspace\Application\DTOs\Output\WorkspaceResponseDTO;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class CreateWorkspaceService
{
    public function __construct(private ApplicationService $applicationService) {}
    public function __invoke(CreateWorkspaceDTO $input): WorkspaceResponseDTO
    {
        ['workspace' => $workspace] = $this->applicationService->runAndCapture(CreateWorkspaceCommand::class, ['accountId' => AccountId::fromString($input->accountId), 'name' => $input->name, 'slug' => $input->slug])->getOrFail();
        return WorkspaceResponseDTO::fromDomain($workspace);
    }
}
