<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminIncidentReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, AdminIncidentReportService $reports): View
    {
        $filters = $request->validate([
            'period' => ['nullable', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $period = $filters['period'] ?? 'daily';
        $timezone = config('app.timezone', 'UTC');
        $dateValue = $filters['date'] ?? CarbonImmutable::now($timezone)->toDateString();
        $selectedDate = CarbonImmutable::createFromFormat('!Y-m-d', $dateValue, $timezone);

        return view('admin.reports.index', $reports->generate($period, $selectedDate));
    }
}
