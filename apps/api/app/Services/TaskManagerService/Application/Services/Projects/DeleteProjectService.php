<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\Application\Services\Projects;

use App\Services\TaskManagerService\Application\Commands\Projects\DeleteProject\DeleteProjectCommand;
use App\Services\TaskManagerService\Application\DTOs\Input\DeleteProjectDTO;
use App\Services\TaskManagerService\Application\DTOs\Output\DeleteProjectResponseDTO;
use App\Services\TaskManagerService\Domain\ValueObjects\ProjectId;
use App\Services\TaskManagerService\Domain\ValueObjects\UserId;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class DeleteProjectService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(DeleteProjectDTO $input): DeleteProjectResponseDTO
    {
        ['projectId' => $projectId] = $this->applicationService->runAndCapture(DeleteProjectCommand::class, [
            'projectId' => ProjectId::fromString($input->projectId),
            'ownerId' => UserId::fromString($input->ownerId),
        ])->getOrFail();

        return new DeleteProjectResponseDTO(true, $projectId);
    }
}
