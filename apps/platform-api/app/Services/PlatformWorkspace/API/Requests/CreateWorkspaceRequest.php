<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Requests;

use Zolta\Http\Request\BaseRequest;

final class CreateWorkspaceRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;
    public function authorize(): bool { return $this->hasAuthenticatedIdentity(); }
    public function rules(): array { return ['name' => ['required', 'string', 'max:120'], 'slug' => ['sometimes', 'nullable', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/']]; }
    public function trustedData(): array { return ['accountId' => $this->authenticatedAccountId()]; }
}
