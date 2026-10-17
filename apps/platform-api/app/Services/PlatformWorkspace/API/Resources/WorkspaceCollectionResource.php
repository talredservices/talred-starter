<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Resources;

use Zolta\Http\Response\Resources\Resource;

final class WorkspaceCollectionResource extends Resource
{
    public function toArray(): array { return $this->all(); }
}
