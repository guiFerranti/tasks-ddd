<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CreateTaskDTO;
use App\Application\DTOs\UpdateTaskDTO;
use App\Application\UseCases\Tasks\CreateTaskUseCase;
use App\Application\UseCases\Tasks\DeleteTaskUseCase;
use App\Application\UseCases\Tasks\UpdateTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Users\Entities\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, CreateTaskUseCase $useCase)
    {
        $dto = new CreateTaskDTO(
            $request->input('title'),
            $request->input('description'),
            TaskStatus::from($request->input('status')),
            User::findOrFail($request->input('assigned_to'))
        );

        try {
            $task = $useCase->execute(auth()->user(), $dto);
            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, Task $task, UpdateTaskUseCase $useCase)
    {
        $dto = new UpdateTaskDTO(
            $request->input('title'),
            $request->input('description'),
            TaskStatus::from($request->input('status')),
        );

        try {
            $updatedTask = $useCase->execute($task, $dto);
            return response()->json($updatedTask);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(Task $task, DeleteTaskUseCase $useCase)
    {
        try {
            $useCase->execute(auth()->user(), $task);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
