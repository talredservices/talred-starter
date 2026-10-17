<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class DeleteProjectResponseDTO extends ResponseDTO
{
    public function __construct(public readonly bool $deleted, public readonly string $project_id) {}
}
