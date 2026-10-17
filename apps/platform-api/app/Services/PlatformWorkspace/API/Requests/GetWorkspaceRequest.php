<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Requests;

use Zolta\Http\Request\BaseRequest;

final class GetWorkspaceRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    public function routeParams(): array
    {
        return ['workspaceId' => ['type' => 'string']];
    }

    public function rules(): array
    {
        return ['workspaceId' => ['required', 'uuid']];
    }

    public function trustedData(): array
    {
        return ['accountId' => $this->authenticatedAccountId()];
    }
}
