<?php

namespace App\Services;

class SimplePdfReportWriter
{
    /** @param array<string, mixed> $report */
    public function write(array $report): string
    {
        $lines = [
            'RESQLINK - DISPATCHER INCIDENT REPORT',
            'Dispatcher: '.$report['dispatcher'],
            'Date range: '.$report['from']->format('M d, Y').' to '.$report['to']->format('M d, Y'),
            'Generated: '.$report['generatedAt']->format('M d, Y h:i A').' Asia/Manila',
            'Filters: '.collect($report['filters'])->filter()->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)).': '.$value)->implode(' | '),
            'Total handled: '.$report['total'].' | Average resolution: '.$this->duration($report['averageMinutes']),
            'Fastest: '.$this->duration($report['fastestMinutes']).' | Slowest: '.$this->duration($report['slowestMinutes']),
            'By status: '.$this->counts($report['byStatus']),
            'By category: '.$this->counts($report['byCategory']),
            'By severity: '.$this->counts($report['bySeverity']),
            'By barangay: '.$this->counts($report['byBarangay']),
            str_repeat('-', 110),
            'INCIDENT   REPORTED AT          CATEGORY       STATUS       SEVERITY     BARANGAY         REPORTER',
            str_repeat('-', 110),
        ];
        foreach ($report['rows'] as $row) {
            $lines[] = sprintf('%-10s %-20s %-14s %-12s %-12s %-16s %-20s',
                $row['reference'], $row['reported_at']?->format('M d, Y h:i A') ?? '-',
                $row['category'] ?? '-', $row['status'] ?? '-', $row['severity'] ?? '-',
                $row['barangay'] ?? '-', $row['reporter'] ?? '-');
            $lines[] = '  Stage minutes: Reported-Received '.($row['reported_received_minutes'] ?? '-').'; Received-Dispatched '.($row['received_dispatched_minutes'] ?? '-').'; Dispatched-Completed '.($row['dispatched_completed_minutes'] ?? '-').'; Total '.($row['resolution_minutes'] ?? '-');
            $lines[] = '  History: '.collect($row['history'])->map(fn ($entry) => $entry['status'].' / '.$entry['who'].' / '.$entry['at']?->format('M d, Y h:i A'))->implode(' | ');
        }
        if ($report['total'] === 0) $lines[] = 'No incidents found for the selected date range.';

        return $this->buildPdf($lines);
    }

    private function duration(?int $minutes): string
    {
        return $minutes === null ? 'N/A' : sprintf('%dh %02dm', intdiv($minutes, 60), $minutes % 60);
    }

    /** @param \Illuminate\Support\Collection<string, int> $counts */
    private function counts($counts): string
    {
        return $counts->map(fn ($count, $label) => $label.': '.$count)->implode(', ') ?: 'None';
    }

    /** @param list<string> $lines */
    private function buildPdf(array $lines): string
    {
        $pages = array_chunk($lines, 43);
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>'];
        $pageRefs = [];
        $next = 4;
        foreach ($pages as $pageLines) {
            $pageId = $next++;
            $streamId = $next++;
            $pageRefs[] = $pageId.' 0 R';
            $stream = "BT\n/F1 8 Tf\n32 560 Td\n11 TL\n";
            foreach ($pageLines as $line) {
                $text = $this->pdfText(mb_strimwidth($line, 0, 130));
                $stream .= '('.$text.") Tj\nT*\n";
            }
            $stream .= "ET\n";
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$streamId.' 0 R >>';
            $objects[$streamId] = '<< /Length '.strlen($stream).">>\nstream\n".$stream.'endstream';
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageRefs).'] /Count '.count($pageRefs).' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }
        $xref = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        return $pdf."trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function pdfText(string $text): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: '';
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $ascii);
    }
}
