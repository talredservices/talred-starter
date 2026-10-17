<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\Application\Commands\Projects\DeleteProject;

use App\Services\TaskManagerService\Domain\Exceptions\ProjectNotFound;
use App\Services\TaskManagerService\Domain\Repositories\ProjectRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(DeleteProjectCommand::class)]
final readonly class DeleteProjectCommandHandler
{
    public function __construct(private ProjectRepository $projects) {}

    public function __invoke(DeleteProjectCommand $command): Result
    {
        if (! $this->projects->deleteOwned($command->projectId, $command->ownerId)) {
            return Result::failure(new ProjectNotFound());
        }

        return Result::success(['projectId' => $command->projectId->toString()]);
    }
}
