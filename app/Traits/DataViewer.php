<?php

namespace App\Traits;

use App\Libraries\CustomQueryBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait DataViewer
{
    /**
     * @param $query
     * @param array|null $priorityOrder
     * @return mixed
     */
    public function scopeAdvancedFilter($query, ?array $priorityOrder = null)
    {
        $request = request();
        $processedQuery = $this->process($query, $request->all(), $priorityOrder);

        return $request->boolean('no_limit')
            ? $processedQuery->get()
            : $processedQuery->paginate($request->input('limit', 10));
    }

    public function process($query, $data, ?array $priorityOrder = null)
    {
        validator($data, [
            'order_column' => 'sometimes|required|in:' . $this->columnList($this->orderable),
            'order_direction' => 'sometimes|required|in:asc,desc',
            'limit' => 'sometimes|required|integer|min:1',
            'filter_match' => 'sometimes|required|in:and,or',
            'f' => 'sometimes|required|array',
            'f.*.column' => 'required|in:' . $this->columnList($this->allowedFilters),
            'f.*.operator' => 'required_with:f.*.column|in:' . $this->allowedOperators(),
            'f.*.query_1' => 'required',
            'f.*.query_2' => 'required_if:f.*.operator,between,not_between'
        ])->validate();

        $query = (new CustomQueryBuilder)->apply($query, $data);
        $query = $this->applyOrder($query, $data);

        return $this->applyPriorityOrder($query, $priorityOrder);
    }

    protected function applyOrder($query, array $data)
    {
        if (empty($data['order_column'])) return $query;

        $column = $data['order_column'];
        $direction = $data['order_direction'] ?? 'asc';

        //  Descarta cualquier orderBy previo
        $query->reorder();

        // Columna propia de la tabla
        if (!str_contains($column, '.')) {
            return $query->orderBy($this->getTable() . '.' . $column, $direction);
        }

        [$relationName, $relatedColumn] = explode('.', $column, 2);

        if (!method_exists($this, $relationName)) return $query;

        $relation = $this->$relationName();

        if (!$relation instanceof BelongsTo) return $query;

        $related = $relation->getRelated();

        $subQuery = $related->newQuery()
            ->select($related->getTable() . '.' . $relatedColumn)
            ->whereColumn(
                $related->getTable() . '.' . $relation->getOwnerKeyName(),
                $this->getTable() . '.' . $relation->getForeignKeyName()
            )
            ->limit(1);

        return $query->orderBy($subQuery, $direction);
    }

    /**
     * Antepone un ORDER BY por grupos: los registros cuyo valor de columna
     * esté en 'values' van primero (0), el resto después de (1).
     * Los órdenes existentes se conservan como criterio secundario.
     *
     * @param $query
     * @param array|null $priorityOrder
     * @return void
     */
    protected function applyPriorityOrder($query, ?array $priorityOrder)
    {
        if (empty($priorityOrder['column']) || empty($priorityOrder['values'])) return $query;

        $baseQuery = $query->getQuery();
        $column = $baseQuery->getGrammar()->wrap($this->getTable() . '.' . $priorityOrder['column']);
        $values = implode(',', array_map('intval', $priorityOrder['values']));
        $priority = [
            'type' => 'Raw',
            'sql' => "CASE WHEN {$column} IN ({$values}) THEN 0 ELSE 1 END",
        ];
        $baseQuery->orders = array_merge([$priority], $baseQuery->orders ?? []);

        return $query;
    }

    protected function columnList(array $columns): string
    {
        return implode(',', $columns);
    }

    protected function allowedOperators(): string
    {
        return implode(',', [
            'equal_to',
            'not_equal_to',
            'less_than',
            'greater_than',
            'between',
            'not_between',
            'contains',
            'starts_with',
            'ends_with',
            'in_the_past',
            'in_the_next',
            'in_the_period',
            'less_than_count',
            'greater_than_count',
            'equal_to_count',
            'not_equal_to_count',
            //  Operadores de fecha
            'date_equals',
            'date_between',
            'date_greater_than',
            'date_less_than',
            'date_greater_than_or_equal',
            'date_less_than_or_equal',
        ]);
    }
}
