<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Exceptions;

use RuntimeException;

final class WorkspaceResourceNotFound extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The Workspace resource link was not found.');
    }
}
