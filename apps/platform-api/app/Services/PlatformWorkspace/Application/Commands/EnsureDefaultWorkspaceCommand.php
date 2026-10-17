<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Commands;

use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use Zolta\Cqrs\Commands\Command;

final class EnsureDefaultWorkspaceCommand extends Command
{
    public function __construct(public readonly AccountId $accountId) {}
}
