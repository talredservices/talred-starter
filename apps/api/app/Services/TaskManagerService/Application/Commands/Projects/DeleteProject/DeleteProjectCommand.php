<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\Application\Commands\Projects\DeleteProject;

use App\Services\TaskManagerService\Domain\ValueObjects\ProjectId;
use App\Services\TaskManagerService\Domain\ValueObjects\UserId;
use Zolta\Cqrs\Commands\Command;

final class DeleteProjectCommand extends Command
{
    public function __construct(public readonly ProjectId $projectId, public readonly UserId $ownerId) {}
}
