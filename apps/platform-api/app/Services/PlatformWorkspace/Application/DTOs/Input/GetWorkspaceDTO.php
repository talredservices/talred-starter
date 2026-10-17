<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

final class GetWorkspaceDTO extends InputDTO
{
    public function __construct(
        public string $workspaceId,
        public string $accountId,
    ) {}
}
