<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Controllers;

use App\Services\PlatformWorkspace\API\Requests\EnsureDefaultWorkspaceRequest;
use App\Services\PlatformWorkspace\API\Resources\WorkspaceResource;
use App\Services\PlatformWorkspace\Application\DTOs\Input\EnsureDefaultWorkspaceDTO;
use App\Services\PlatformWorkspace\API\Services\WorkspaceHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('platform/v1/workspaces/default', methods: ['POST'], middleware: ['api', 'identity.introspect'], name: 'platform.workspaces.default')]
#[Request(EnsureDefaultWorkspaceRequest::class, EnsureDefaultWorkspaceDTO::class)]
#[Service(WorkspaceHttpService::class, 'Default workspace ensured.')]
#[Response(WorkspaceResource::class)]
#[Doc(summary: 'Ensure the authenticated account has a default workspace', tags: ['Platform Workspaces'])]
final class EnsureDefaultWorkspaceController extends Controller {}
