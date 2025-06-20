<?php

namespace App\Libraries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomQueryBuilder
{
    public function apply(Builder $query, array $data): Builder
    {
        if (!isset($data['f'])) {
            return $query;
        }

        foreach ($data['f'] as $field) {
            $field['match'] = $field['match'] ?? 'and';
            $this->applyFilter($query, $field);
        }
        return $query;
    }

    public function applyFilter(Builder $query, array $filter): void
    {
        if (str_contains($filter['column'], '.')) {
            $segments = explode('.', $filter['column']);
            $filter['column'] = array_pop($segments);
            $relation = implode('.', $segments);

            if ($filter['column'] === 'count') {
                $this->callOperatorMethod($query, $filter, $relation);
            } else {
                $query->whereHas($relation, function ($query) use ($filter) {
                    $this->callOperatorMethod($query, $filter);
                });
            }
        } else {
            $this->callOperatorMethod($query, $filter);
        }
    }

    public function callOperatorMethod(Builder $query, array $filter, ?string $relation = null): void
    {
        $method = Str::camel($filter['operator']);
        if (method_exists($this, $method)) {
            $relation ? $this->$method($filter, $query, $relation) : $this->$method($filter, $query);
        }
    }

    public function equalTo(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], '=', $f['query_1'], $f['match']);
    }

    public function notEqualTo(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], '!=', $f['query_1'], $f['match']);
    }

    public function lessThan(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], '<', $f['query_1'], $f['match']);
    }

    public function greaterThan(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], '>', $f['query_1'], $f['match']);
    }

    public function between(array $f, Builder $q): Builder
    {
        return $q->whereBetween($f['column'], [$f['query_1'], $f['query_2']], $f['match']);
    }

    public function notBetween(array $f, Builder $q): Builder
    {
        return $q->whereNotBetween($f['column'], [$f['query_1'], $f['query_2']], $f['match']);
    }

    public function contains(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], 'ILIKE', '%' . $f['query_1'] . '%', $f['match']);
    }

    public function startsWith(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], 'ILIKE', '%' . $f['query_1'], $f['match']);
    }

    public function endsWith(array $f, Builder $q): Builder
    {
        return $q->where($f['column'], 'ILIKE', $f['query_2'] . '%', $f['match']);
    }

    public function inThePast(array $f, Builder $q): Builder
    {
        $end = Carbon::now()->endOfMonth();
        $begin = match ($f['query_2']) {
            'hours' => Carbon::now()->subhours($f['query_1']),
            'days' => Carbon::now()->subdays($f['query_1'])->startOfDay(),
            'months' => Carbon::now()->submonths($f['query_1'])->startOfDay(),
            'years' => Carbon::now()->subyears($f['query_1'])->startOfDay(),
            default => Carbon::now()->subDays($f['query_1'])->startOfDay(),
        };

        return $q->whereBetween($f['column'], [$begin, $end], $f['match']);
    }

    public function inTheNext(array $f, Builder $q): Builder
    {
        $begin = Carbon::now()->startOfDay();
        $end = match ($f['query_2']) {
            'hours' => Carbon::now()->addHours($f['query_1']),
            'days' => Carbon::now()->addDays($f['query_1'])->endOfDay(),
            'months' => Carbon::now()->addMonths($f['query_1'])->endOfDay(),
            'years' => Carbon::now()->addYears($f['query_1'])->endOfDay(),
            default => Carbon::now()->addDays($f['query_1'])->endOfDay(),
        };
        return $q->whereBetween($f['column'], [$begin, $end], $f['match']);
    }

    public function inThePeriod(array $f, Builder $q): Builder
    {
        [$begin, $end] = match ($f['query_2']) {
            'today' => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'yesterday' => [Carbon::now()->subDay()->startOfDay(), Carbon::now()->subDay()->endOfDay()],
            'tomorrow' => [Carbon::now()->addDay()->startOfDay(), Carbon::now()->addDay()->endOfDay()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'next_month' => [Carbon::now()->addMonth()->startOfMonth(), Carbon::now()->addMonth()->endOfMonth()],
            'last_year' => [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'next_year' => [Carbon::now()->addYear()->startOfYear(), Carbon::now()->addYear()->endOfYear()],
            default => [Carbon::now(), Carbon::now()]
        };
        return $q->whereBetween($f['column'], [$begin, $end], $f['match']);
    }

    public function equalToCount(array $f, Builder $q, string $relation): Builder
    {
        return $q->has($relation, '=', $f['query_1']);
    }

    public function notEqualToCount(array $f, Builder $q, string $relation): Builder
    {
        return $q->has($relation, '!=', $f['query_1']);
    }

    public function lessThanCount(array $f, Builder $q, string $relation): Builder
    {
        return $q->has($relation, '<', $f['query_1']);
    }

    public function greaterThanCount(array $f, Builder $q, string $relation): Builder
    {
        return $q->has($relation, '>', $f['query_1']);
    }
}
