<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Entities;

use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceResourceReference;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceResourceLinkId;
use DateTimeInterface;
use InvalidArgumentException;

final readonly class WorkspaceResourceLink
{
    public function __construct(
        public WorkspaceResourceLinkId $id,
        public string $workspaceId,
        public WorkspaceResourceReference $resource,
        public string $linkedBy,
        public DateTimeInterface $createdAt,
    ) {
        foreach (['Workspace id' => $workspaceId, 'account id' => $linkedBy] as $label => $value) {
            if (! self::isUuid($value)) {
                throw new InvalidArgumentException("A Workspace resource link requires a valid {$label}.");
            }
        }
    }

    /** @return array<string, mixed> */
    public function persistenceRecord(): array
    {
        return [
            'id' => $this->id->toString(),
            'workspace_id' => $this->workspaceId,
            'resource_type' => $this->resource->type->value,
            'external_resource_id' => $this->resource->externalId,
            'linked_by' => $this->linkedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
