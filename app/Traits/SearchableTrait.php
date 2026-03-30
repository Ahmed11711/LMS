<?php

namespace App\Traits;

trait Searchable
{

    public function getSearchableColumns(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }


    public function getSearchableRelations(): array
    {
        return property_exists($this, 'searchableRelations') ? $this->searchableRelations : [];
    }
}
