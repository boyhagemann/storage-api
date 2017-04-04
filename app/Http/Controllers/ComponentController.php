<?php

namespace App\Http\Controllers;

use App\Component;
use Helpers\QueryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ComponentController extends Controller
{
    /**
     * @var Component
     */
    protected $components;

    /**
     * ComponentController constructor.
     *
     * @param Component $repository
     */
    public function __construct(Component $model)
    {
        $this->components = $model;
    }

    /**
     * @return array
     */
    public function index(Request $request)
    {
        $and = Collection::make($request->get('and', []));
        $or = Collection::make($request->get('or', []));

        if($request->has('q')) {
            $q = $request->get('q');
            $parts = explode(' ', $q);
            Collection::make($parts)
                ->map(function($part) use ($and, $or) {

                    $field = strstr($part, ':') ? substr($part, 0, strpos($part, ':')) : 'label';
                    $value = strstr($part, ':') ? substr($part, strpos($part, ':') + 1) : $part;
                    $cast = 'string';

                    // Field transform
                    switch($field) {

                        case 'type':
                        case 'types':
                        case 'has_nodes':
                            $field = 'data.properties.' . $field;
                            break;
                    }

                    // Cast
                    switch($field) {

                        case 'has_nodes':
                            $cast = 'boolean';
                            break;

                        case 'types':
                        case 'uses':
                            $cast = 'list';
                            break;
                    }

                    $and->push(compact('field', 'value', 'cast'));
                });
        }


        $and = QueryHelper::transform($and->toArray());
        $or = QueryHelper::transform($or->toArray());
//        dd(compact('and', 'or'));


        return $this->components->find(compact('and', 'or'));
    }

    /**
     * @param $component
     * @return array
     */
    public function show($component)
    {
        return $this->components->get($component);
    }

    /**
     * @param Request $request
     */
    public function store(Request $request, $id)
    {
        return $this->components->upsert($id, $request->all());
    }
}
