<?php

namespace App\Console\Commands;

use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyMockDefenseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mock-defense:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify mock defense database counts and records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== MOCK DEFENSE DATA VERIFICATION ===');
        $this->newLine();

        // 1. Responders
        $responders = User::query()
            ->whereRelation('role', 'name', 'volunteer')
            ->with('volunteerProfile')
            ->get();

        $this->info("1. Barangay Responders (Total: {$responders->count()})");
        $this->table(
            ['ID', 'Name', 'Username', 'Email', 'Barangay', 'Status'],
            $responders->map(fn ($u) => [
                $u->id,
                $u->name,
                $u->username,
                $u->email,
                $u->volunteerProfile?->barangay ?? 'N/A',
                $u->volunteerProfile?->verification_status ?? 'N/A',
            ]),
        );
        $this->newLine();

        // 2. Incident Counts by Category
        $byCategory = Incident::query()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $this->info('2. Incidents per Category (Target: >= 20 each)');
        $this->table(
            ['Category', 'Count', 'Requirement Met?'],
            $byCategory->map(fn ($r) => [
                $r->category,
                $r->total,
                $r->total >= 20 ? 'YES' : 'NO',
            ]),
        );
        $this->newLine();

        // 3. Incident Counts by Status
        $byStatus = Incident::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $this->info('3. Incidents per Status');
        $this->table(['Status', 'Count'], $byStatus->map(fn ($r) => [$r->status, $r->total]));
        $this->newLine();

        // 4. Incident Counts by Severity
        $bySeverity = Incident::query()
            ->select('severity', DB::raw('count(*) as total'))
            ->groupBy('severity')
            ->get();

        $this->info('4. Incidents per Severity');
        $this->table(['Severity', 'Count'], $bySeverity->map(fn ($r) => [$r->severity, $r->total]));
        $this->newLine();

        // 5. Incident History Summary & Sample Transition Trails
        $historyCount = IncidentHistory::query()->count();
        $this->info("5. Incident History Transitions (Total records: {$historyCount})");

        $statuses = ['Reported', 'Received', 'Dispatched', 'Completed'];
        foreach ($statuses as $st) {
            $sampleIncident = Incident::query()->where('status', $st)->with(['history.status', 'history.dispatcher'])->first();
            if ($sampleIncident !== null) {
                $this->line("<comment>Sample {$st} Incident (INC-" . str_pad($sampleIncident->id, 4, '0', STR_PAD_LEFT) . " - {$sampleIncident->category}):</comment>");
                $trails = $sampleIncident->history->sortBy('changed_at')->map(fn ($h) => [
                    'Step' => $h->status?->name,
                    'By' => $h->dispatcher?->name ?? 'System',
                    'Time' => $h->changed_at->format('M j, Y g:i A'),
                    'Notes' => $h->notes,
                ]);
                $this->table(['Status Step', 'Changed By', 'Timestamp', 'Notes'], $trails);
            }
        }

        $this->info('Verification complete!');
        return self::SUCCESS;
    }
}
