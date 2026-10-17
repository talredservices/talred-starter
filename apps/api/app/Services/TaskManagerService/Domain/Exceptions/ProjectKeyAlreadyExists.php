<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\Domain\Exceptions;

use DomainException;

final class ProjectKeyAlreadyExists extends DomainException
{
    public function __construct()
    {
        parent::__construct('You already have a project with this key. Choose another key.');
    }
}
