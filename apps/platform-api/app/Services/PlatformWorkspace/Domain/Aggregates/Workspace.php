<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Aggregates;

use App\Services\PlatformWorkspace\Domain\Enums\WorkspaceStatus;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use InvalidArgumentException;
use Zolta\Domain\Aggregates\AggregateRoot;

final class Workspace extends AggregateRoot
{
    private function __construct(
        private WorkspaceId $id,
        private AccountId $ownerId,
        private string $name,
        private string $slug,
        private WorkspaceStatus $status,
    ) {}

    public static function create(AccountId $ownerId, string $name, string $slug, ?WorkspaceId $id = null): self
    {
        $name = trim($name);
        $slug = self::normalizeSlug($slug === '' ? $name : $slug);
        if ($name === '' || mb_strlen($name) > 120) {
            throw new InvalidArgumentException('Workspace name must be between 1 and 120 characters.');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || mb_strlen($slug) > 64) {
            throw new InvalidArgumentException('Workspace slug must contain lowercase letters, numbers, and single dashes.');
        }
        return new self($id ?? WorkspaceId::default(), $ownerId, $name, $slug, WorkspaceStatus::Active);
    }

    public static function restore(WorkspaceId $id, AccountId $ownerId, string $name, string $slug, WorkspaceStatus $status): self
    {
        return new self($id, $ownerId, $name, $slug, $status);
    }

    public function rename(string $name, string $slug): void
    {
        if ($this->status !== WorkspaceStatus::Active) {
            throw new InvalidArgumentException('Archived workspaces cannot be renamed.');
        }
        $updated = self::create($this->ownerId, $name, $slug, $this->id);
        $this->name = $updated->name;
        $this->slug = $updated->slug;
    }

    public function archive(): void
    {
        if ($this->status === WorkspaceStatus::Archived) {
            return;
        }
        $this->status = WorkspaceStatus::Archived;
    }

    public static function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }

    public function id(): WorkspaceId { return $this->id; }
    public function ownerId(): AccountId { return $this->ownerId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function status(): WorkspaceStatus { return $this->status; }
}
