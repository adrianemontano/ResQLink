<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class XlsxReportWriter
{
    /** @param array<string, mixed> $report */
    public function write(array $report): string
    {
        if (! class_exists(ZipArchive::class)) throw new RuntimeException('PHP ZIP extension is required for XLSX export.');
        $rows = [
            ['ResQLink Dispatcher Incident Report'],
            ['Dispatcher', $report['dispatcher']],
            ['Date range', $report['from']->format('M d, Y').' - '.$report['to']->format('M d, Y')],
            ['Generated', $report['generatedAt']->format('M d, Y h:i A').' Asia/Manila'],
            ['Filters', collect($report['filters'])->filter()->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)).': '.$value)->implode(' | ')],
            ['Total handled', $report['total']], ['Average resolution (minutes)', $report['averageMinutes'] ?? 'N/A'],
            ['Fastest (minutes)', $report['fastestMinutes'] ?? 'N/A'], ['Slowest (minutes)', $report['slowestMinutes'] ?? 'N/A'], [],
            ['Breakdown by status'],
        ];
        foreach ($report['byStatus'] as $label => $count) $rows[] = [$label, $count];
        $rows[] = ['Breakdown by category'];
        foreach ($report['byCategory'] as $label => $count) $rows[] = [$label, $count];
        $rows[] = ['Breakdown by severity'];
        foreach ($report['bySeverity'] as $label => $count) $rows[] = [$label, $count];
        $rows[] = ['Breakdown by barangay'];
        foreach ($report['byBarangay'] as $label => $count) $rows[] = [$label, $count];
        $rows[] = [];
        $rows[] = ['Incident', 'Reported at', 'Reporter', 'Category', 'Severity', 'Status', 'Barangay', 'Reported-Received min', 'Received-Dispatched min', 'Dispatched-Completed min', 'Total resolution min', 'Status history'];
        foreach ($report['rows'] as $row) {
            $rows[] = [
                $row['reference'], $row['reported_at']?->format('M d, Y h:i A') ?? '', $row['reporter'],
                $row['category'], $row['severity'], $row['status'], $row['barangay'],
                $row['reported_received_minutes'] ?? '', $row['received_dispatched_minutes'] ?? '',
                $row['dispatched_completed_minutes'] ?? '', $row['resolution_minutes'] ?? '',
                collect($row['history'])->map(fn ($entry) => $entry['status'].' / '.$entry['who'].' / '.$entry['at']?->format('M d, Y h:i A'))->implode(' | '),
            ];
        }
        if ($report['total'] === 0) $rows[] = ['No incidents found for the selected date range.'];

        $path = tempnam(sys_get_temp_dir(), 'resqlink-report-');
        if ($path === false) throw new RuntimeException('Could not create temporary XLSX file.');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Could not create XLSX archive.');
        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($rows));
        $zip->close();
        $bytes = file_get_contents($path);
        unlink($path);
        if ($bytes === false) throw new RuntimeException('Could not read generated XLSX file.');
        return $bytes;
    }

    /** @param list<list<mixed>> $rows */
    private function worksheet(array $rows): string
    {
        $xmlRows = [];
        foreach ($rows as $rowIndex => $row) {
            $cells = [];
            foreach ($row as $columnIndex => $value) {
                $reference = $this->column($columnIndex + 1).($rowIndex + 1);
                if (is_int($value) || is_float($value)) $cells[] = '<c r="'.$reference.'"><v>'.$value.'</v></c>';
                else $cells[] = '<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $value).'</t></is></c>';
            }
            $xmlRows[] = '<row r="'.($rowIndex + 1).'">'.implode('', $cells).'</row>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.implode('', $xmlRows).'</sheetData></worksheet>';
    }

    private function column(int $number): string
    {
        $name = '';
        while ($number > 0) { $remainder = ($number - 1) % 26; $name = chr(65 + $remainder).$name; $number = intdiv($number - 1, 26); }
        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Dispatcher Report" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
    }
}
