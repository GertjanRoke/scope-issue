## installation

1. `git clone git@github.com:GertjanRoke/scope-issue.git`
2. `cd scope-issue`
3. `./vendor/bin/sail up`
4. `./vendor/bin/sail artisan migrate --seed`


## Problem

When applying a `->select()` to a relation that also has a global scope that uses a `->addSelect()` gives different result compared to a direct query with the same code.

The global scope on the `Item` model looks like this:
```php
protected static function booted()
{
    static::addGlobalScope('sort', function ($query) {
        $modelClass = new static();
        $query
            ->addSelect([
                "{$modelClass->getTable()}.*",
                'sortables.left',
            ])
            ->leftJoin('sortables', function ($join) use ($query, $modelClass) {
                $join->on('sortables.sortable_id', '=', "{$modelClass->getTable()}.id")
                    ->where('sortables.sortable_type', '=', $modelClass->getMorphClass());
            });
    });
}
```

Direct query:
```php
use \App\Models\Item;

items::query()
    ->select([
        'items.id',
        'name',
    ])
    ->get();

// Will build this query which is expected
select `items`.`id`, `name`, `items`.*, `sortables`.`left` from `items` left join `sortables` on `sortables`.`sortable_id` = `items`.`id` and `sortables`.`sortable_type` = 'App\\Models\\Item'

items::query()
    ->applyScopes()
    ->withoutGlobalScope('sort')
    ->select([
        'items.id',
        'name',
    ])
    ->get();

// Will build this query which is expected
select `items`.`id`, `name` from `items` left join `sortables` on `sortables`.`sortable_id` = `items`.`id` and `sortables`.`sortable_type` = 'App\\Models\\Item'
```
> Note I needed to apply the scope and then remove the global scope to affect the columns I want to select.

But if you load it as a relation and call the same methods for the global scope the result is different:
```php
use \App\Models\Category;
use \App\Models\Item;

Category::query()
    ->with([
        'items' => function (HasMany $query) {
            $query
                ->applyScopes()
                ->withoutGlobalScope('sort')
                ->select([
                    'name',
                ]);
        },
    ])
    ->select([
        'categories.id',
        'name',
    ])
    ->get();

// Will build this query
select `id`, `name` from `categories`

select `items`.*, `sortables`.`left` from `items` left join `sortables` on `sortables`.`sortable_id` = `items`.`id` and `sortables`.`sortable_type` = 'App\\Models\\Item' where `items`.`category_id` in (1, 2, 3, 4)
```

As you can see the select is not even affected, so I also have an example without the applying of the global scope and this makes the `->select()` behave like a `->addSelect()`.
```php
use \App\Models\Category;
use \App\Models\Item;

Category::query()
    ->with([
        'items' => function (HasMany $query) {
            $query
                ->select([
                    'name',
                ]);
        },
    ])
    ->select([
        'categories.id',
        'name',
    ])
    ->get();

// Will build this query
select `id`, `name` from `categories`

select `name`, `items`.*, `sortables`.`left` from `items` left join `sortables` on `sortables`.`sortable_id` = `items`.`id` and `sortables`.`sortable_type` = 'App\\Models\\Item' where `items`.`category_id` in (1, 2, 3, 4)
```
