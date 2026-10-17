<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Requests;

use App\Services\TaskManagerService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

final class DeleteProjectRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool { return $this->hasAuthenticatedIdentity(); }
    public function routeParams(): array { return ['projectId' => ['type' => 'string']]; }
    public function rules(): array { return ['projectId' => ['required', 'uuid']]; }
    public function trustedData(): array { return ['ownerId' => $this->authenticatedUserId()]; }
}
