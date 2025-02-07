<?php

namespace Modules\LMS\Repositories;

use Modules\LMS\Models\Currency;

class CurrencyRepository extends BaseRepository
{
    protected static $model = Currency::class;

    protected static $exactSearchFields = [];

    protected static $rules = [
        'save' => [
            'name' => 'required|unique:currencies,name',
        ],
        'update' => [],
    ];

    /**
     * @param  int  $id
     * @param  array  $data
     */
    public static function update($id, $data): array
    {
        static::$rules['update'] = [
            'name' => 'required|unique:currencies,name,' . $id,
        ];

        return parent::update($id, $data);
    }
}
