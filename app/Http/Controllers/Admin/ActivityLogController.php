<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // filter by roles
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        // filter by actions
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

         // search the school id, the action, the description or the person's name
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('school_id_num', 'like', "%$q%")
                  ->orWhere('action', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
                  ->orWhereHas('user', function ($u) use ($q) {
                      // CONCAT lets a full name like "Maria Santos" match
                      $u->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$q%"]);
                  });
            });
        }

         // date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // ordered by newest and limit of 20 rows
        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.logs', ['logs' => $logs, 'actions' => $actions]);
    }
}
