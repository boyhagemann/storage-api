<?php

namespace App\Http\Controllers;

use Boyhagemann\Storage\Contracts\Entity;
use Boyhagemann\Storage\Contracts\EntityRepository;
use Boyhagemann\Storage\Contracts\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Helpers\DataBuilder;

class ComponentController extends Controller
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var Record
     */
    protected $records;

    public function __construct(EntityRepository $entityRepository, Record $records)
    {
        $this->entity = $entityRepository->get('component');
        $this->records = $records;
    }

    /**
     * @return array
     */
    public function index()
    {
        return $this->records->find($this->entity);
    }

    /**
     * @param $id
     * @return array
     */
    public function show($id)
    {
        return $this->records->get($this->entity, $id);
    }

    /**
     * @param Request $request
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            '_id' => 'required|string',
        ]);

        $id = $request->get('_id');
        $payload = $request->except(['_id']);

        $this->records->upsert($this->entity, $id, $payload);
    }

    /**
     * @param $id
     * @param $node
     * @return Collection
     */
    public function build($id, $node)
    {
        // Find the component record
        $component = $this->records->get($this->entity, $id);

        // Find the dependencies
        $dependencies = $component['uses']
            ? $this->records->find($this->entity, [
                ['_id', 'IN', $component['uses']],
            ])
            : [];

        // Merge the component and its dependencies together
        $components = array_merge([$component], $dependencies);

        // We are only interested in the 'data' property of the stored component
        $data = Collection::make($components)
            ->map(function(Array $component) { return $component['data']; })
            ->toArray();

        return DataBuilder::build($data, $node);
    }
}
