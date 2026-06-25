<?php

namespace App\Livewire\Traits;

trait WithSorting
{
    public string $sortField = 'date';
    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Apply the active sort to the query. The sort column is whitelisted
     * against $allowed to prevent arbitrary column names from the client,
     * and a stable id tiebreaker is appended so rows with equal values
     * (e.g. same date) always keep a deterministic order.
     */
    protected function applySort($query, array $allowed, string $default = 'date')
    {
        $field = in_array($this->sortField, $allowed, true) ? $this->sortField : $default;
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query->orderBy($field, $direction);

        if ($field !== 'id') {
            $query->orderBy('id', $direction);
        }

        return $query;
    }
}
