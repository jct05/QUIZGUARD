<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
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

        // Date range filter (inclusive of full start/end days; swaps if reversed)
        $fromDate = null;
        $toDate = null;

        if ($request->filled('from')) {
            try {
                $fromDate = Carbon::parse($request->from)->startOfDay();
            } catch (\Throwable $e) {
                $fromDate = null;
            }
        }

        if ($request->filled('to')) {
            try {
                $toDate = Carbon::parse($request->to)->endOfDay();
            } catch (\Throwable $e) {
                $toDate = null;
            }
        }

        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            $swappedFrom = $toDate->copy()->startOfDay();
            $swappedTo = $fromDate->copy()->endOfDay();
            $fromDate = $swappedFrom;
            $toDate = $swappedTo;
        }

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        // ordered by newest and limit of 20 rows
        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.logs', ['logs' => $logs, 'actions' => $actions]);
    }
}
