<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Commands;

use App\Services\PlatformWorkspace\Domain\Aggregates\Workspace;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceRepository;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use InvalidArgumentException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(EnsureDefaultWorkspaceCommand::class)]
final readonly class EnsureDefaultWorkspaceCommandHandler
{
    public function __construct(private WorkspaceRepository $workspaces) {}

    public function __invoke(EnsureDefaultWorkspaceCommand $command): Result
    {
        $this->workspaces->lockDefaultProvisioning($command->accountId);
        $existingDefault = $this->workspaces->findDefault($command->accountId);
        if ($existingDefault !== null) {
            return Result::success(['workspace' => $existingDefault]);
        }

        $workspace = $this->workspaces->findFirstOwnedActive($command->accountId);
        $slug = 'my-workspace-'.substr(str_replace('-', '', $command->accountId->toString()), 0, 12);
        if ($workspace === null) {
            $workspace = Workspace::create($command->accountId, 'My workspace', $slug);
        } else {
            if ($workspace->name() !== 'My workspace' || $workspace->slug() !== $slug) {
                if ($this->workspaces->slugExists($slug, $workspace->id())) {
                    return Result::failure(new InvalidArgumentException('The default workspace slug is already in use.'));
                }
                $workspace->rename('My workspace', $slug);
            }
        }

        if ($this->workspaces->slugExists($workspace->slug(), $workspace->id())) {
            return Result::failure(new InvalidArgumentException('The default workspace slug is already in use.'));
        }

        $this->workspaces->save($workspace);
        $this->workspaces->assignDefault($command->accountId, $workspace->id());
        $view = $this->workspaces->findAccessible($workspace->id(), $command->accountId);

        return $view === null
            ? Result::failure(new InvalidArgumentException('Unable to resolve the default workspace.'))
            : Result::success(['workspace' => $view]);
    }
}
