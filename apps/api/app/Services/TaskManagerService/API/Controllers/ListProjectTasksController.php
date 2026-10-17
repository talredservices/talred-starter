<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\ListProjectTasksRequest;
use App\Services\TaskManagerService\API\Resources\ProjectTasksResource;
use App\Services\TaskManagerService\Application\DTOs\Input\ListProjectTasksDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('projects/{projectId}/tasks', methods: ['GET'], middleware: ['api', 'identity.introspect:account.read'], name: 'projects.tasks.index')]
#[Request(ListProjectTasksRequest::class, ListProjectTasksDTO::class)]
#[Service(TaskManagerHttpService::class, 'Project tasks retrieved.')]
#[Response(ProjectTasksResource::class)]
#[Doc(summary: 'List tasks in an owned project', tags: ['Tasks'])]
final class ListProjectTasksController extends Controller {}
