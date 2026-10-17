<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Requests;

use App\Services\PlatformWorkspace\Application\DTOs\Input\ListWorkspacesDTO;
use Zolta\Http\Request\BaseRequest;

final class ListWorkspacesRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;
    public function authorize(): bool { return $this->hasAuthenticatedIdentity(); }
    public function rules(): array { return []; }
    public function trustedData(): array { return ['accountId' => $this->authenticatedAccountId()]; }
}
