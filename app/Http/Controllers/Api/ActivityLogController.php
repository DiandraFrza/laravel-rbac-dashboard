<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActivityLog::class);

        return response()->json(ActivityLog::latest('logged_at')->get());
    }
}