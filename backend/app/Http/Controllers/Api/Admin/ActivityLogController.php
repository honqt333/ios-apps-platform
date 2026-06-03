<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) min((int) $request->query('per_page', 30), 100);

        $query = ActivityLog::query()->with(['causer:id,name,email', 'subject']);

        if ($causerId = $request->query('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        if ($subjectType = $request->query('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($logName = $request->query('log_name')) {
            $query->where('log_name', $logName);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
