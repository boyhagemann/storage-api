<?php namespace Helpers;

use App\Exceptions\NotFound;
use Illuminate\Support\Collection;

class Store extends \ArrayObject
{
    /**
     * @param string $key
     * @param Collection $collection
     * @return $this
     */
    public function set($key, Collection $collection)
    {
        $this->offsetSet($key, $collection);

        return $this;
    }

    /**
     * @param $key
     * @return Collection
     */
    public function get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * @param $path
     * @param array $query
     * @return Collection
     */
    public function find($path, Array $query)
    {
        $collection = $this->offsetGet($path);

        return $collection
            ->filter(function(Array $item) use ($query) {

                foreach($query as $key => $value) {

                    if(is_array($value) && !in_array($item[$key], $value)) {
                        return false;
                    }

                    if(!is_array($value) && (!array_key_exists($key, $item) || $item[$key] !== $value)) {
                        return false;
                    }
                }

                return true;
            });
    }

    /**
     * @param $path
     * @param array $query
     * @return array|null
     */
    public function first($path, Array $query)
    {
        $found = $this->find($path, $query);

        return $found->count() ? $found->first() : null;
    }

    /**
     * @param $path
     * @param array $query
     * @return array|null
     * @throws NotFound
     */
    public function firstOrFail($path, Array $query)
    {
        if(!$found = $this->first($path, $query)) {
            throw new NotFound(sprintf('No item found for path "%s" and query "%s"', $path, json_encode($query)));
        }

        return $found;
    }

    /**
     * @param $path
     * @param $id
     * @param array $item
     * @return $this
     */
    public function update($path, $id, Array $item)
    {
        $current = $this->get($path)->offsetGet($id);
        $merged = array_merge($current, $item);

        $this->get($path)->offsetSet($id, $merged);

        return $this;
    }

    /**
     * @param array $components
     * @return Store
     */
    public static function fromComponents(Array $components)
    {
        $store = new Store();

        $store->set('components', Collection::make($components)
            ->map(function(Array $component) {
                return $component['properties'];
            }));

        $store->set('fields', static::makeCollection($components, 'fields'));
        $store->set('nodes', static::makeCollection($components, 'nodes'));
        $store->set('edges', static::makeCollection($components, 'edges'));

        return $store;
    }

    /**
     * @param array $components
     * @param string $property
     * @return Collection
     */
    public static function makeCollection(Array $components, $property)
    {
        return Collection::make($components)
            ->filter(function(Array $data) use ($property) {
                return isset($data[$property]);
            })
            ->flatMap(function(Array $data) use ($property) {
                return $data[$property];
            })
            ->unique('__id')
            ->keyBy('__id');
    }
}