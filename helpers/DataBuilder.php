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

        // Do some event like things to alter the store items
        static::transform($store);

        $stack = new SplStack();
        $data = new Collection();

        $stack->push($nodeId);

        while (!$stack->isEmpty()) {
            static::buildNode($stack->pop(), $stack, $data, $store);
        }

        return $data->values();
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
            '_type' => $component['type'] ?: $component['__id'],
        ];

        $fields = $store
            ->find('fields', ['component' => $node['type']])
            ->sortBy('order');

        $edges = $store->find('edges', ['from' => $nodeId]);

        foreach($fields as $field) {

            $item[$field['name']] = $field['collection']
                ? static::buildFieldCollection($field, $stack, $edges, $store)
                : static::buildField($field, $edges, $store);
        }

        $data->push($item);
    }

    public static function transform(Store $store)
    {
        // Find the decorators
        $store
            ->find('nodes', ['purpose' => 'decorator'])
            ->each(function(Array $decorator) use ($store) {
                static::transformDecorator($decorator, $store);
            });

    }

    public static function transformDecorator(Array $decoratorNode, Store $store)
    {
        $decoratorEdge = $store
            ->firstOrFail('edges', ['to' => $decoratorNode['__id']]);

        $store->update('nodes', $decoratorNode['__id'], [
            'purpose' => 'entity',
        ]);

        $store
            ->find('edges', ['from' => $decoratorEdge['from']])
            ->filter(function(Array $edge) use ($decoratorEdge) {
                return $edge['__id'] !== $decoratorEdge['__id'];
            })
            ->each(function(Array $edge) use ($decoratorNode, $store) {
//                var_dump($edge);
                $store->update('edges', $edge['__id'], [
                    'from' => $decoratorNode['__id'],
                    'thru' => $decoratorNode['field'],
                ]);
            });
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


//            // We don't want property nodes here, only entities and decorators
//            ->filter(function(Array $edge) use ($store) {
//                return $store->first('nodes', [
//                    '__id' => $edge['to'],
//                ]);
//            })

            // Add the node to the stack to be processed
            ->map(function(Array $edge) use ($stack) {

                // Add this node to the stack to be processed.
                // Add it to the beginning of the stack to keep the correct order.
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