<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComponentController extends BaseController
{
    /**
     * @return array
     */
    public function index()
    {
        return $this->repository->find();
    }

    /**
     * @param $component
     * @return array
     */
    public function show($component)
    {
        return $this->repository->get($component);
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            '_id' => 'required|string',
        ]);

        $id = $request->get('_id');
        $payload = $request->except(['_id']);

        $this->repository->upsert($id, $payload);
    }
}
