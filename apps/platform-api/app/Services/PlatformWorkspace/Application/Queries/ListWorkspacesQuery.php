<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Queries;

use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use Zolta\Cqrs\Queries\Query;

final class ListWorkspacesQuery extends Query
{
    public function __construct(public readonly AccountId $accountId) {}
}
