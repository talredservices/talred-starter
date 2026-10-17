<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

final class DeleteProjectDTO extends InputDTO
{
    public function __construct(public readonly string $projectId, public readonly string $ownerId) {}
}
