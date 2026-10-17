<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Controllers;

use App\Services\PlatformWorkspace\Application\Services\WorkspaceIdentityProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ListIdentityProjectDirectoryController
{
    public function __construct(private WorkspaceIdentityProjectService $identityProjects) {}

    public function __invoke(Request $request): JsonResponse
    {
        $accessToken = (string) $request->bearerToken();
        abort_if($accessToken === '', 401, 'An Identity access token is required for this operation.');

        return response()->json(['data' => $this->identityProjects->identityProjectDirectory($accessToken)]);
    }
}
