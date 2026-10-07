<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'period' => ['nullable', 'in:daily,weekly,monthly'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $period = in_array($request->query('period'), ['daily', 'weekly', 'monthly'], true)
            ? $request->query('period') : 'daily';
        $date = Carbon::parse($request->query('date', now()->toDateString()));
        [$from, $to] = match ($period) {
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };
        $scoped = Incident::query()->whereBetween('reported_at', [$from, $to]);

        return view('admin.reports.index', [
            'period' => $period, 'date' => $date->toDateString(),
            'from' => $from, 'to' => $to,
            'totalIncidents' => (clone $scoped)->count(),
            'byCategory' => (clone $scoped)
                ->select('category', DB::raw('count(*) as total'))
                ->groupBy('category')
                ->orderByDesc('total')
                ->get(),
            'byBarangay' => (clone $scoped)
                ->select('barangay', DB::raw('count(*) as total'))
                ->groupBy('barangay')->orderByDesc('total')->get(),
            'byStatus' => (clone $scoped)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->orderByDesc('total')
                ->get(),
            'byFrequency' => (clone $scoped)
                ->selectRaw('DATE(reported_at) as report_date, count(*) as total')
                ->groupBy('report_date')->orderBy('report_date')->get(),
        ]);
    }
}
