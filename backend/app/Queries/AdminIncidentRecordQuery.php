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
            ->with('reporter')
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
     * Match the search term against the incident ID, searchable columns, and
     * the reporting volunteer's name.
     *
     * @param  Builder<Incident>  $query
     */
    private static function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            foreach (static::SEARCHABLE_COLUMNS as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }

            $query->orWhere('id', $search)->orWhereHas(
                'reporter',
                fn (Builder $reporter) => $reporter->where('name', 'like', "%{$search}%"),
            );
        });
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

