@extends('layouts.dashboard', ['title' => 'Admin Dashboard | ResQLink'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard-analytics.css') }}">
@endpush

@section('content')
    <div class="header-row admin-dashboard-heading">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Operational overview for account administration, Barangay Responder readiness, and incident response.</p>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">Create user</a>
    </div>

    <div class="admin-dashboard">
        <section class="admin-metrics" aria-label="Operational summary">
            <article class="card admin-metric-card">
                <span class="admin-metric-mark mark-red" aria-hidden="true"></span>
                <span class="admin-metric-label">Total incidents</span>
                <strong>{{ number_format($totalIncidents) }}</strong>
                <small>All incident reports</small>
            </article>
            <article class="card admin-metric-card">
                <span class="admin-metric-mark mark-orange" aria-hidden="true"></span>
                <span class="admin-metric-label">Open incidents</span>
                <strong>{{ number_format($openIncidents) }}</strong>
                <small>Awaiting completion</small>
            </article>
            <article class="card admin-metric-card">
                <span class="admin-metric-mark mark-green" aria-hidden="true"></span>
                <span class="admin-metric-label">Completed</span>
                <strong>{{ number_format($completedIncidents) }}</strong>
                <small>Closed response reports</small>
            </article>
            <article class="card admin-metric-card">
                <span class="admin-metric-mark mark-blue" aria-hidden="true"></span>
                <span class="admin-metric-label">Barangay Responders</span>
                <strong>{{ number_format($totalVolunteers) }}</strong>
                <small>{{ number_format($verifiedVolunteers) }} verified</small>
            </article>
            <article class="card admin-metric-card">
                <span class="admin-metric-mark mark-purple" aria-hidden="true"></span>
                <span class="admin-metric-label">Needs verification</span>
                <strong>{{ number_format($pendingVolunteerVerification) }}</strong>
                <small>Barangay Responder accounts to review</small>
            </article>
            <article class="card admin-metric-card">
                <span class="admin-metric-mark mark-slate" aria-hidden="true"></span>
                <span class="admin-metric-label">Dispatchers</span>
                <strong>{{ number_format($totalDispatchers) }}</strong>
                <small>Dispatcher accounts</small>
            </article>
        </section>

        <section class="admin-analytics-grid" aria-label="Incident analytics">
            <article class="card admin-analytics-card admin-trend-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-eyebrow">Incident activity</span>
                        <h2>Incident trend</h2>
                    </div>
                    <span class="admin-period-label">Last 6 months</span>
                </div>
                <div class="admin-trend-chart" role="img" aria-label="Monthly incident totals: @foreach ($incidentTrend as $month) {{ $month['label'] }} {{ $month['total'] }}; @endforeach">
                    @foreach ($incidentTrend as $month)
                        <div class="admin-trend-column">
                            <span class="admin-trend-value">{{ $month['total'] }}</span>
                            <div class="admin-trend-track">
                                <span style="--bar-height: {{ $month['total'] > 0 ? (int) round($month['total'] / $trendTotal * 100) : 0 }}%"></span>
                            </div>
                            <span class="admin-trend-label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="card admin-analytics-card admin-category-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-eyebrow">Report distribution</span>
                        <h2>Incidents by category</h2>
                    </div>
                </div>
                @php
                    $categoryColors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];
                    $categoryStops = [];
                    $categoryProgress = 0;
                    foreach ($categoryBreakdown as $index => $category) {
                        $segmentEnd = $totalIncidents > 0 ? $categoryProgress + ($category['value'] / $totalIncidents * 100) : $categoryProgress;
                        $categoryStops[] = $categoryColors[$index % count($categoryColors)] . ' ' . $categoryProgress . '% ' . $segmentEnd . '%';
                        $categoryProgress = $segmentEnd;
                    }
                @endphp
                @if ($categoryBreakdown->isNotEmpty())
                    <div class="admin-category-layout">
                        <div class="admin-category-donut" style="--category-chart: conic-gradient({{ implode(', ', $categoryStops) }})" role="img" aria-label="Incident category distribution">
                            <span>{{ number_format($totalIncidents) }}</span>
                            <small>Total</small>
                        </div>
                        <div class="admin-category-legend">
                            @foreach ($categoryBreakdown as $index => $category)
                                <div class="admin-category-item">
                                    <span class="admin-category-dot" style="--category-color: {{ $categoryColors[$index % count($categoryColors)] }}" aria-hidden="true"></span>
                                    <span>{{ $category['label'] }}</span>
                                    <strong>{{ $category['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="admin-empty-state">Category analytics will appear when incidents are reported.</div>
                @endif
            </article>

            <article class="card admin-analytics-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-eyebrow">Current workload</span>
                        <h2>Incidents by status</h2>
                    </div>
                </div>
                <div class="admin-breakdown-list">
                    @forelse ($statusBreakdown as $status)
                        @php
                            $statusColor = match (strtolower($status['label'])) {
                                'reported' => 'var(--status-reported)',
                                'received' => 'var(--status-received)',
                                'dispatched' => 'var(--status-dispatched)',
                                'completed' => 'var(--status-completed)',
                                default => 'var(--chart-5)',
                            };
                        @endphp
                        <div class="admin-breakdown-row">
                            <div class="admin-breakdown-label">
                                <span>{{ $status['label'] }}</span>
                                <strong>{{ $status['value'] }}</strong>
                            </div>
                            <div class="admin-breakdown-track">
                                <span style="width: {{ $totalIncidents > 0 ? ($status['value'] / $totalIncidents) * 100 : 0 }}%; --bar-color: {{ $statusColor }}"></span>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state">Status analytics will appear when incidents are reported.</div>
                    @endforelse
                </div>
            </article>

            <article class="card admin-analytics-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-eyebrow">Preliminary assessment</span>
                        <h2>Severity mix</h2>
                    </div>
                </div>
                <div class="admin-breakdown-list">
                    @forelse ($severityBreakdown as $severity)
                        @php
                            $severityColor = match (strtolower($severity['label'])) {
                                'low' => 'var(--severity-low)',
                                'moderate' => 'var(--severity-moderate-bg)',
                                'high' => 'var(--severity-high)',
                                'critical' => 'var(--severity-critical)',
                                default => 'var(--chart-5)',
                            };
                        @endphp
                        <div class="admin-breakdown-row">
                            <div class="admin-breakdown-label">
                                <span>{{ $severity['label'] }}</span>
                                <strong>{{ $severity['value'] }}</strong>
                            </div>
                            <div class="admin-breakdown-track">
                                <span style="width: {{ $totalIncidents > 0 ? ($severity['value'] / $totalIncidents) * 100 : 0 }}%; --bar-color: {{ $severityColor }}"></span>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state">Severity analytics will appear when incidents are reported.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="admin-bottom-grid">
            <article class="card admin-analytics-card admin-recent-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-eyebrow">Latest activity</span>
                        <h2>Recent incidents</h2>
                    </div>
                    <a class="admin-text-link" href="{{ route('admin.incidents.index') }}">View all <span aria-hidden="true">&rarr;</span></a>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-recent-table">
                        <thead>
                            <tr>
                                <th scope="col">Incident</th>
                                <th scope="col">Category</th>
                                <th scope="col">Location</th>
                                <th scope="col">Status</th>
                                <th scope="col">Reported</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentIncidents as $incident)
                                <tr>
                                    <td><a href="{{ route('admin.incidents.show', $incident) }}">INC-{{ str_pad((string) $incident->getKey(), 4, '0', STR_PAD_LEFT) }}</a></td>
                                    <td>{{ $incident->category ?: 'Other' }}</td>
                                    <td>{{ $incident->barangay ?: 'Not provided' }}</td>
                                    <td><span class="admin-status-label">{{ $incident->status ?: 'Unassigned' }}</span></td>
                                    <td>{{ ($incident->reported_at ?? $incident->created_at)->format('M j, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td class="admin-table-empty" colspan="5">No incidents have been reported yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="card admin-analytics-card admin-actions-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-eyebrow">Shortcuts</span>
                        <h2>Quick actions</h2>
                    </div>
                </div>
                <a class="admin-action-link" href="{{ route('admin.incidents.index') }}">
                    <span class="admin-action-mark action-incidents" aria-hidden="true">01</span>
                    <span><strong>Review incidents</strong><small>Browse the incident record log</small></span>
                    <span class="admin-action-arrow" aria-hidden="true">&rarr;</span>
                </a>
                <a class="admin-action-link" href="{{ route('admin.users.index') }}">
                    <span class="admin-action-mark action-users" aria-hidden="true">02</span>
                    <span><strong>Manage accounts</strong><small>Review Barangay Responder and dispatcher users</small></span>
                    <span class="admin-action-arrow" aria-hidden="true">&rarr;</span>
                </a>
                <a class="admin-action-link" href="{{ route('admin.reports.index') }}">
                    <span class="admin-action-mark action-reports" aria-hidden="true">03</span>
                    <span><strong>Open reports</strong><small>View incident reporting summaries</small></span>
                    <span class="admin-action-arrow" aria-hidden="true">&rarr;</span>
                </a>
            </article>
        </section>
    </div>
@endsection
