<?php

namespace App\Http\Controllers;

use App\Component;
use Illuminate\Support\Collection;
use Helpers\DataBuilder;

class NodeController extends BaseController
{
    /**
     * @param $component
     * @param $node
     * @return Collection
     */
    public function build($component, $node)
    {
        // Find the component record
        $component = $this->repository->get($component);

        // Find the dependencies
        $dependencies = $component['uses']
            ? $this->repository->find([
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
