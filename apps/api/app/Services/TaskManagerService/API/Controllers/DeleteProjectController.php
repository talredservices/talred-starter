<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\DeleteProjectRequest;
use App\Services\TaskManagerService\API\Resources\DeletedProjectResource;
use App\Services\TaskManagerService\Application\DTOs\Input\DeleteProjectDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('projects/{projectId}', methods: ['DELETE'], middleware: ['api', 'identity.introspect:account.update'], name: 'projects.destroy')]
#[Request(DeleteProjectRequest::class, DeleteProjectDTO::class)]
#[Service(TaskManagerHttpService::class, 'Project and its tasks deleted.')]
#[Response(DeletedProjectResource::class)]
#[Doc(summary: 'Delete an owned project and its tasks', tags: ['Projects'])]
final class DeleteProjectController extends Controller {}
