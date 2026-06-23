<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{

    public $filterable = ['id', 'pages_id'];


    public function pages()
    {
        return $this->belongsTo(Pages::class, 'pages_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SectionItems::class, 'section_id');
    }
}
