<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Resources;

use Zolta\Http\Response\Resources\Resource;

final class DeletedProjectResource extends Resource
{
    public function toArray(): array
    {
        return [
            'deleted' => $this->get('deleted'),
            'project_id' => $this->get('project_id'),
        ];
    }
}
