<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Exceptions;

use RuntimeException;

final class WorkspaceSlugAlreadyExists extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('A workspace with this slug already exists.');
    }
}
