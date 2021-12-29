<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope('sort', function ($query) {
            $modelClass = new static();
            $query->addSelect([
                "{$modelClass->getTable()}.*",
                'sortables.left',
            ])->leftJoin('sortables', function ($join) use ($query, $modelClass) {
                $join->on('sortables.sortable_id', '=', "{$modelClass->getTable()}.id")
                    ->where('sortables.sortable_type', '=', $modelClass->getMorphClass());
            });
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sortable()
    {
        return $this->morphOne(Sortable::class, 'sortable');
    }
}
