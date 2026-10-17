<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Commands;

use App\Services\PlatformWorkspace\Domain\Aggregates\Workspace;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceSlugAlreadyExists;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(CreateWorkspaceCommand::class)]
final readonly class CreateWorkspaceCommandHandler
{
    public function __construct(private WorkspaceRepository $workspaces) {}
    public function __invoke(CreateWorkspaceCommand $command): Result
    {
        $workspace = Workspace::create($command->accountId, $command->name, $command->slug ?? $command->name);
        if ($this->workspaces->slugExists($workspace->slug())) {
            return Result::failure(new WorkspaceSlugAlreadyExists());
        }
        $this->workspaces->save($workspace);
        return Result::success(['workspace' => $workspace]);
    }
}
