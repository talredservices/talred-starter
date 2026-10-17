<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\ValueObjects;

use App\Services\PlatformWorkspace\Domain\Enums\WorkspaceResourceType;
use InvalidArgumentException;

final readonly class WorkspaceResourceReference
{
    public function __construct(
        public WorkspaceResourceType $type,
        public string $externalId,
    ) {
        if ($externalId === '' || mb_strlen($externalId) > 191) {
            throw new InvalidArgumentException('A Workspace resource reference must have a valid external id.');
        }

        if ($type === WorkspaceResourceType::IdentityProject && ! self::isUuid($externalId)) {
            throw new InvalidArgumentException('An Identity Project resource reference must use a UUID.');
        }
    }

    public static function identityProject(string $identityProjectId): self
    {
        return new self(WorkspaceResourceType::IdentityProject, $identityProjectId);
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
