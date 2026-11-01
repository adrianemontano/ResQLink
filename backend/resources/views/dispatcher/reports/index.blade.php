@extends('layouts.dashboard', ['title' => 'Dispatcher Reports | ResQLink'])

@section('content')
    <div class="header-row">
        <div><h1>Incident Handling Reports</h1><p>Incidents with status activity recorded by your dispatcher account.</p></div>
        <div class="actions" style="margin:0">
            <a class="button secondary" href="{{ route('dispatcher.reports.export', ['format' => 'pdf'] + request()->query()) }}">Download PDF</a>
            <a class="button" href="{{ route('dispatcher.reports.export', ['format' => 'xlsx'] + request()->query()) }}">Download Excel (.xlsx)</a>
        </div>
    </div>

    <form method="GET" action="{{ route('dispatcher.reports.index') }}" class="card filter-row dispatcher-report-filters">
        <label>From<input type="date" name="from" value="{{ $filters['from'] }}" required></label>
        <label>To<input type="date" name="to" value="{{ $filters['to'] }}" required></label>
        <label>Quick range<select name="preset"><option value="custom" @selected($filters['preset'] === 'custom')>Custom range</option><option value="today" @selected($filters['preset'] === 'today')>Today</option><option value="week" @selected($filters['preset'] === 'week')>This week</option><option value="month" @selected($filters['preset'] === 'month')>This month</option></select></label>
        <label>Summary by<select name="group_by"><option value="day" @selected($groupBy === 'day')>Day</option><option value="week" @selected($groupBy === 'week')>Week</option><option value="month" @selected($groupBy === 'month')>Month</option></select></label>
        <label>Category<select name="category"><option value="">All</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>@endforeach</select></label>
        <label>Severity<select name="severity"><option value="">All</option>@foreach (['Critical','High','Moderate','Low'] as $severity)<option @selected($filters['severity'] === $severity)>{{ $severity }}</option>@endforeach</select></label>
        <label>Status<select name="status"><option value="">All</option>@foreach (['Reported','Received','Dispatched','Completed'] as $status)<option @selected($filters['status'] === $status)>{{ $status }}</option>@endforeach</select></label>
        <label>Barangay<select name="barangay"><option value="">All</option>@foreach ($barangays as $barangay)<option @selected($filters['barangay'] === $barangay)>{{ $barangay }}</option>@endforeach</select></label>
        <button type="submit">Apply filters</button>
    </form>

    <div class="report-stat-grid">
        <article class="report-stat"><div class="report-stat-val">{{ $total }}</div><div class="report-stat-label">Total incidents handled</div></article>
        <article class="report-stat"><div class="report-stat-val">{{ $resolvedCount }}</div><div class="report-stat-label">Completed with full timing</div></article>
        <article class="report-stat"><div class="report-stat-val">{{ $averageMinutes === null ? '—' : intdiv($averageMinutes, 60).'h '.($averageMinutes % 60).'m' }}</div><div class="report-stat-label">Average resolution</div></article>
        <article class="report-stat"><div class="report-stat-val">{{ $fastestMinutes === null ? '—' : $fastestMinutes.'m' }} / {{ $slowestMinutes === null ? '—' : $slowestMinutes.'m' }}</div><div class="report-stat-label">Fastest / slowest resolution</div></article>
    </div>

    @if ($total === 0)
        <section class="card empty-state"><h2>No incidents found for the selected date range.</h2><p>Adjust the date range or filters to review incidents you handled.</p></section>
    @else
        <div class="detail-grid">
            <section class="card"><h2>Incidents by reported date</h2>
                @foreach ($timeline as $period => $count)
                    @php($width = max(4, (int) round($count / max($timeline->max(), 1) * 100)))
                    <div class="bar-row"><span class="bar-label">{{ $period }}</span><div class="bar-track"><div class="bar-fill" style="width:{{ $width }}%;background:var(--rosewood)"></div><strong class="bar-value">{{ $count }}</strong></div></div>
                @endforeach
            </section>
            <section class="card"><h2>Distribution by category</h2>
                @foreach ($byCategory as $label => $count)
                    @php($colors = ['var(--rosewood)','var(--sangria)','var(--maximum-red)','var(--tangelo)','var(--philippine-orange)'])
                    @php($index = $loop->index)
                    <div class="bar-row"><span class="bar-label">{{ $label }}</span><div class="bar-track"><div class="bar-fill" style="width:{{ max(4, (int) round($count / max($total, 1) * 100)) }}%;background:{{ $colors[$index % count($colors)] }}"></div><strong class="bar-value">{{ $count }}</strong></div></div>
                @endforeach
            </section>
        </div>

        <div class="detail-grid" style="margin-top:var(--space-4)">
            @foreach (['byStatus' => 'By status', 'bySeverity' => 'By severity', 'byBarangay' => 'By barangay'] as $key => $title)
                <section class="card"><h2>{{ $title }}</h2><dl class="detail-list">@forelse ($$key as $label => $count)<dt>{{ $label ?: 'Unknown' }}</dt><dd>{{ $count }}</dd>@empty<p>No records.</p>@endforelse</dl></section>
            @endforeach
        </div>

        <section class="card" style="margin-top:var(--space-4)"><h2>Handled incidents and stage durations</h2>
            <div class="table-scroll"><table><thead><tr><th>Incident</th><th>Reported</th><th>Category / Barangay</th><th>Severity / Status</th><th>Reported→Received</th><th>Received→Dispatched</th><th>Dispatched→Completed</th><th>Total</th><th>History</th></tr></thead><tbody>
                @foreach ($rows as $row)
                    <tr><td><a href="{{ route('dispatcher.incidents.show', $row['id']) }}">{{ $row['reference'] }}</a><br>{{ $row['reporter'] }}</td><td>{{ $row['reported_at']?->format('M d, Y, h:i A') ?? '—' }}</td><td>{{ $row['category'] }}<br>{{ $row['barangay'] }}</td><td>{{ $row['severity'] }}<br>{{ $row['status'] }}</td><td>{{ $row['reported_received_minutes'] ?? '—' }} min</td><td>{{ $row['received_dispatched_minutes'] ?? '—' }} min</td><td>{{ $row['dispatched_completed_minutes'] ?? '—' }} min</td><td>{{ $row['resolution_minutes'] ?? '—' }} min</td><td>@foreach ($row['history'] as $entry)<small>{{ $entry['status'] }} · {{ $entry['who'] }} · {{ $entry['at']?->format('M d, h:i A') }}<br></small>@endforeach</td></tr>
                @endforeach
            </tbody></table></div>
        </section>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.dispatcher-report-filters');
    if (!form) return;
    const preset = form.querySelector('[name="preset"]');
    const from = form.querySelector('[name="from"]');
    const to = form.querySelector('[name="to"]');
    const manilaToday = () => {
        const parts = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
        const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
        return new Date(Date.UTC(Number(values.year), Number(values.month) - 1, Number(values.day)));
    };
    const dateString = (date) => `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, '0')}-${String(date.getUTCDate()).padStart(2, '0')}`;
    preset.addEventListener('change', () => {
        const now = manilaToday();
        let start = new Date(now);
        let end = new Date(now);
        if (preset.value === 'week') {
            const mondayOffset = (now.getUTCDay() + 6) % 7;
            start.setUTCDate(now.getUTCDate() - mondayOffset);
            end = new Date(start);
            end.setUTCDate(start.getUTCDate() + 6);
        } else if (preset.value === 'month') {
            start = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), 1));
            end = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth() + 1, 0));
        } else if (preset.value === 'custom') return;
        from.value = dateString(start);
        to.value = dateString(end);
    });
    [from, to].forEach((input) => input.addEventListener('change', () => { preset.value = 'custom'; }));
});
</script>
@endpush
