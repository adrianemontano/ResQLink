<?php

namespace App\Queries;

use App\Models\Incident;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Read-only query builder for the admin incident-record workspace.
 *
 * Centralises record search, category/status filtering, and archive scoping so
 * the admin controller, tests, and future export features share one definition
 * of "which records an admin is looking at". Records are never modified here.
 */
class AdminIncidentRecordQuery
{
    /** Incident fields an admin may search across. */
    private const SEARCHABLE_COLUMNS = [
        'category',
        'barangay',
        'landmark',
        'nearest_landmark',
        'notes',
        'status',
        'severity',
    ];

    /** Numeric incident fields matched as text (e.g. "42", "10.3157"). */
    private const NUMERIC_COLUMNS = [
        'affected_population',
        'persons_count',
        'latitude',
        'longitude',
        'impact_radius',
    ];

    /**
     * Apply search, category, and status filters plus the record scope.
     *
     * @param  Builder<Incident>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Incident>
     */
    public static function apply(Builder $query, array $filters, string $scope = 'all'): Builder
    {
        return static::scopeRecords(
            static::applyFilters($query, $filters),
            $scope,
        );
    }

    /**
     * Apply the free-text search and the category/status dropdown filters.
     *
     * @param  Builder<Incident>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Incident>
     */
    public static function applyFilters(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return $query
            ->with(['reporter', 'volunteer'])
            ->when($search !== '', fn (Builder $query) => static::applySearch($query, $search))
            ->when(
                filled($filters['category'] ?? null),
                fn (Builder $query) => $query->where('category', $filters['category']),
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where('status', $filters['status']),
            );
    }

    /**
     * Match the search term against the incident ID (plain or INC-####),
     * text and numeric columns, the report time, and the reporter's identity.
     *
     * @param  Builder<Incident>  $query
     */
    private static function applySearch(Builder $query, string $search): void
    {
        $identifier = static::incidentIdentifier($search);
        $pattern = "%{$search}%";
        $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [$search];

        $query->where(function (Builder $query) use ($identifier, $pattern, $terms): void {
            if ($identifier !== null) {
                $query->whereKey($identifier);
            }

            foreach (static::SEARCHABLE_COLUMNS as $column) {
                $query->orWhere($column, 'like', $pattern);
            }

            foreach (static::NUMERIC_COLUMNS as $column) {
                $query->orWhereRaw("CAST({$column} AS CHAR) LIKE ?", [$pattern]);
            }

            $query->orWhereRaw(
                'CAST(COALESCE(incidents.reported_at, incidents.created_at) AS CHAR) LIKE ?',
                [$pattern],
            );

            foreach (['reporter', 'volunteer'] as $relationship) {
                $query->orWhereHas($relationship, fn (Builder $reporter) => static::matchReporter($reporter, $terms));
            }
        });
    }

    /**
     * Require every search term to match one of the reporter's identity fields.
     *
     * @param  array<int, string>  $terms
     */
    private static function matchReporter(Builder $reporter, array $terms): void
    {
        foreach ($terms as $term) {
            $reporter->where(function (Builder $fields) use ($term): void {
                foreach (['name', 'first_name', 'last_name', 'email', 'username'] as $column) {
                    $fields->orWhere($column, 'like', "%{$term}%");
                }
            });
        }
    }

    /** Resolve "42" or "INC-0042" to an incident ID. */
    private static function incidentIdentifier(string $search): ?int
    {
        if (ctype_digit($search)) {
            return (int) $search;
        }

        return preg_match('/^INC-(\d+)$/i', $search, $matches) === 1 ? (int) $matches[1] : null;
    }

    /**
     * Restrict results to the active or archived portion of the record log.
     *
     * Completed incidents represent the archived/historical portion of the log
     * and remain fully readable without any data change.
     *
     * @param  Builder<Incident>  $query
     * @return Builder<Incident>
     */
    public static function scopeRecords(Builder $query, string $scope): Builder
    {
        return match ($scope) {
            'active' => $query->whereNot('status', Incident::STATUS_COMPLETED),
            'archived' => $query->where('status', Incident::STATUS_COMPLETED),
            default => $query,
        };
    }

    /**
     * Total, active, and archived record counts for the scope switcher.
     *
     * @return array{all: int, active: int, archived: int}
     */
    public static function countsByScope(): array
    {
        $archived = Incident::query()->where('status', Incident::STATUS_COMPLETED)->count();

        return [
            'all' => Incident::query()->count(),
            'active' => Incident::query()->whereNot('status', Incident::STATUS_COMPLETED)->count(),
            'archived' => $archived,
        ];
    }
}

