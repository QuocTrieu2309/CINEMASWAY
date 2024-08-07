<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    protected $limit = 1000;
    protected $order = 'desc';
    protected $sort = 'id';

    //    Paginate
    public function handleLimit($key, $initialValue)
    {
        $limit = (is_numeric($key) ?
            ($key >= 10 && $key <= 50 ? $key : $initialValue) :
            $initialValue
        ) ??
            $initialValue;
        return $limit;
    }

    public function handleFilter(array $data, $key, $initialValue)
    {
        $filter = (collect($data)->contains($key) ?
            $key :
            $initialValue
        ) ?? $initialValue;
        return $filter;
    }
}
