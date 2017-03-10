<?php namespace Helpers;

use Boyhagemann\Storage\Contracts\Entity;
use Boyhagemann\Storage\Contracts\Record;
use Illuminate\Support\Collection;

class ComponentHelper
{
    /**
     * @param array $component
     * @param Entity $entity
     * @param Record $records
     * @return array
     */
    public static function getDependencies(Array $component, Entity $entity, Record $records)
    {
        // Get the components in use in this component
        $uses = static::scanForDependencies($component);

        if($uses->count()) {

            // Fetch these components...
            $components = $records->find($entity, [
                ['_id', 'IN', $uses->toArray()],
            ]);

            foreach($components as $component) {
                foreach ($component['uses'] as $id) {
                    $uses->push($id);
                }
            }
        }

        // Get the recursive component usages
        return $uses->unique()->values()->toArray();
    }

    /**
     * @param array $component
     * @return Collection
     */
    public static function scanForDependencies(Array $component)
    {
        // Scan for usage of other components
        $uses = Collection::make([]);

        if(isset($component['type'])) {
            $uses->push($component['type']);
        }

        if(isset($component['types'])) {
            foreach($component['types'] as $id) {
                $uses->push($id);
            }
        }

        if(isset($component['fields'])) {
            Collection::make($component['fields'])
                ->map(function(Array $field) use ($uses) {
                    foreach($field['accepts'] as $id) {
                        $uses->push($id);
                    }
                });
        }

        if(isset($component['nodes'])) {
            Collection::make($component['nodes'])
                ->map(function(Array $node) use ($uses) {
                    $uses->push($node['type']);
                });
        }

        return $uses->unique()->values();
    }
}