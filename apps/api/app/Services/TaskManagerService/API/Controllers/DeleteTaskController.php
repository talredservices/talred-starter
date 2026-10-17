<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\DeleteTaskRequest;
use App\Services\TaskManagerService\API\Resources\DeletedTaskResource;
use App\Services\TaskManagerService\Application\DTOs\Input\DeleteTaskDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('tasks/{taskId}', methods: ['DELETE'], middleware: ['api', 'identity.introspect:account.update'], name: 'tasks.destroy')]
#[Request(DeleteTaskRequest::class, DeleteTaskDTO::class)]
#[Service(TaskManagerHttpService::class, 'Task deleted.')]
#[Response(DeletedTaskResource::class)]
#[Doc(summary: 'Delete an owned task', tags: ['Tasks'])]
final class DeleteTaskController extends Controller {}
