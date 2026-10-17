<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Exceptions;

use RuntimeException;

final class WorkspaceResourcePermissionDenied extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Workspace owner or administrator access is required.');
    }
}
