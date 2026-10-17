<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\UpdateTaskRequest;
use App\Services\TaskManagerService\API\Resources\TaskResource;
use App\Services\TaskManagerService\Application\DTOs\Input\UpdateTaskDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('tasks/{taskId}', methods: ['PATCH'], middleware: ['api', 'identity.introspect:account.update'], name: 'tasks.update')]
#[Request(UpdateTaskRequest::class, UpdateTaskDTO::class)]
#[Service(TaskManagerHttpService::class, 'Task updated.')]
#[Response(TaskResource::class)]
#[Doc(summary: 'Update an owned task', tags: ['Tasks'])]
final class UpdateTaskController extends Controller {}
