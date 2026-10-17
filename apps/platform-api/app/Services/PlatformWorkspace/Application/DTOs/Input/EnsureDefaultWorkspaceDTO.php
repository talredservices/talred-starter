<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

final class EnsureDefaultWorkspaceDTO extends InputDTO
{
    public function __construct(public readonly string $accountId) {}
}
