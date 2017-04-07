<?php namespace App;

use Boyhagemann\Storage\Contracts\EntityRepository;
use Boyhagemann\Storage\Contracts\Entity;
use Boyhagemann\Storage\Contracts\RecordRepository;
use Boyhagemann\Storage\Contracts\Collection;
use Helpers\ComponentHelper;

/**
 * Class Component
 * @package App
 * @method get( string $id, array $options = [])
 * @method Collection find( array $query = [], array $options = [])
 * @method first( array $query = [], array $options = [])
 * @method insert( array $data, array $options = [])
 * @method update( string $id, array $data, array $options = [])
 * @method updateWhere( array $query, array $data, array $options = [])
 * @method delete( string $id, array $options = [])
 * @method deleteWhere( array $query, array $options = [])
 */
class Component
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var RecordRepository
     */
    protected $records;

    /**
     * ComponentController constructor.
     *
     * @param EntityRepository $entities
     * @param RecordRepository $records
     */
    public function __construct(EntityRepository $entities, RecordRepository $records)
    {
        $this->entity = $entities->get('component');
        $this->records = $records;
    }

    /**
     * @param $id
     * @param array $component
     * @param array $options
     * @return mixed
     */
    public function upsert($id, Array $component, Array $options = [])
    {
        // Find the component usages inside the component
        $component['uses'] = ComponentHelper::getDependencies($component, $this->entity, $this->records);

        return $this->records->upsert($this->entity, $id, $component, $options);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        array_unshift($arguments, $this->entity);

        return call_user_func_array([$this->records, $name], $arguments);
    }
}