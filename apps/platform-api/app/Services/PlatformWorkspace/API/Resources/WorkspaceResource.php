<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Resources;

use Zolta\Http\Response\Resources\Resource;

final class WorkspaceResource extends Resource
{
    public function toArray(): array { return ['workspace' => $this->get('workspace')?->toArray() ?? $this->all()]; }
}
