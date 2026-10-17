<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Controllers;

use App\Services\PlatformWorkspace\Application\Services\IdentityApplicationDirectoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ListIdentityApplicationDirectoryController
{
    public function __construct(private IdentityApplicationDirectoryService $applications) {}

    public function __invoke(Request $request): JsonResponse
    {
        $accessToken = (string) $request->bearerToken();
        abort_if($accessToken === '', 401, 'An Identity access token is required for this operation.');

        return response()->json(['data' => $this->applications->list($accessToken)]);
    }
}
