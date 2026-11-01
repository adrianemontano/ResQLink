<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Services\DispatcherIncidentReportService;
use App\Services\SimplePdfReportWriter;
use App\Services\XlsxReportWriter;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DispatcherReportController extends Controller
{
    public function index(Request $request, DispatcherIncidentReportService $reports): View
    {
        return view('dispatcher.reports.index', $reports->generate(
            (int) $request->user()->id,
            $this->filters($request),
        ));
    }

    public function export(
        Request $request,
        string $format,
        DispatcherIncidentReportService $reports,
        XlsxReportWriter $xlsx,
        SimplePdfReportWriter $pdf,
    ): HttpResponse {
        abort_unless(in_array($format, ['pdf', 'xlsx'], true), 404);
        $data = $reports->generate((int) $request->user()->id, $this->filters($request));
        $filename = 'dispatcher-report-'.$data['from']->format('Ymd').'-'.$data['to']->format('Ymd');

        return $format === 'xlsx'
            ? Response::make($xlsx->write($data), 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.xlsx"',
            ])
            : Response::make($pdf->write($data), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
            ]);
    }

    /** @return array<string, string|null> */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'preset' => ['nullable', Rule::in(['today', 'week', 'month', 'custom'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'category' => ['nullable', 'string', 'max:80'],
            'severity' => ['nullable', Rule::in(['Low', 'Moderate', 'High', 'Critical'])],
            'status' => ['nullable', Rule::in(['Reported', 'Received', 'Dispatched', 'Completed'])],
            'barangay' => ['nullable', 'string', 'max:100'],
            'group_by' => ['nullable', Rule::in(['day', 'week', 'month'])],
        ]);
        $today = CarbonImmutable::now('Asia/Manila');
        $preset = $data['preset'] ?? 'today';
        [$from, $to] = match ($preset) {
            'week' => [$today->startOfWeek()->toDateString(), $today->endOfWeek()->toDateString()],
            'month' => [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()],
            'custom' => [$data['from'] ?? $today->toDateString(), $data['to'] ?? $today->toDateString()],
            default => [$today->toDateString(), $today->toDateString()],
        };

        return [
            'preset' => $preset, 'from' => $from, 'to' => $to,
            'category' => $data['category'] ?? null, 'severity' => $data['severity'] ?? null,
            'status' => $data['status'] ?? null, 'barangay' => $data['barangay'] ?? null,
            'group_by' => $data['group_by'] ?? 'day',
        ];
    }
}
