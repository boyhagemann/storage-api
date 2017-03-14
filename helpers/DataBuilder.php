<?php namespace Helpers;

use Illuminate\Support\Collection;
use SplStack;

class DataBuilder
{
    /**
     * @param array $components
     * @param $nodeId
     * @return Collection
     */
    public static function build(Array $components, $nodeId)
    {
        $store = Store::fromComponents($components);
        $stack = new SplStack();
        $data = new Collection();

        $stack->push($nodeId);

        while (!$stack->isEmpty()) {
            static::buildNode($stack->pop(), $stack, $data, $store);
        }

        return $data;
    }

    /**
     * @param $nodeId
     * @param SplStack $stack
     * @param Collection $data
     * @param Store $store
     */
    public static function buildNode($nodeId, SplStack $stack, Collection $data, Store $store)
    {
        $node = $store->firstOrFail('nodes', ['__id' => $nodeId]);
        $component = $store->firstOrFail('components', ['__id' => $node['type']]);

        $item = [
            '_id' => $nodeId,
            '_type' => $component['type'] ?: $component['__id']
        ];

        $fields = $store
            ->find('fields', [
                'component' => $node['type'],
            ])->sortBy('order');

        $edges = $store->find('edges', [
            'from' => $nodeId,
        ]);

        foreach($fields as $field) {

            $item[$field['name']] = $field['collection']
                ? static::buildFieldCollection($field, $stack, $edges, $store)
                : static::buildField($field, $edges, $store);
        }

        $data->push($item);
    }

    /**
     * @param array $field
     * @param SplStack $stack
     * @param Collection $edges
     * @param Store $store
     * @return array
     */
    public static function buildFieldCollection(Array $field, SplStack $stack, Collection $edges, Store $store)
    {
        return $edges

            // Find the edges that are mapped to this field
            ->filter(function(Array $edge) use ($field) {
                return $edge['thru'] === $field['__id'];
            })

            // We don't want decorator nodes here...
            ->filter(function(Array $edge) use ($store) {
                return $store->first('nodes', [
                    '__id' => $edge['to'],
                    'purpose' => 'entity',
                ]);
            })

            // Add the node to the stack to be processed
            ->map(function(Array $edge) use ($stack) {

                $stack->unshift($edge['to']);

                return $edge['to'];
            })
            ->values()
            ->toArray();
    }

    /**
     * @param array $field
     * @param Collection $edges
     * @param Store $store
     * @return mixed
     */
    public static function buildField(Array $field, Collection $edges, Store $store)
    {
        $edge = $edges->first(function($edge) use ($field) {
            return $edge['thru'] === $field['__id'];
        });

        $node = $store->first('nodes', [
            '__id' => $edge['to']
        ]);

        return $node ? $node['value'] : $field['default'];
    }

}