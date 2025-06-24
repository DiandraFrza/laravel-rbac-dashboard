<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::with(['creator', 'assignee']);

        if ($user->role === 'admin') {
            
        } 
        else {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        }

        $tasks = $query->latest()->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        // dd($request->all()); 
        
        $this->authorize('create', Task::class);

        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|uuid|exists:users,id',
            'due_date' => 'required|date',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'done'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($user->role === 'manager') {
            $assignee = User::find($request->assigned_to);
            if (!$assignee || $assignee->role !== 'staff') {
                return response()->json(['assigned_to' => ['Manager can only assign tasks to staff.']], 422);
            }
        }
        
        $data = $validator->validated();
        $data['created_by'] = $user->id;

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['creator', 'assignee']);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'sometimes|required|uuid|exists:users,id',
            'due_date' => 'sometimes|required|date',
            'status' => ['sometimes', 'required', Rule::in(['pending', 'in_progress', 'done'])],
        ]);

        $task->update($validatedData);

        return response()->json($task);
    }

    public function destroy(Task $task)
    { 
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}