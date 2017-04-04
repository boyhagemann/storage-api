<?php namespace App;

use Boyhagemann\Storage\Contracts\EntityRepository;
use Boyhagemann\Storage\Contracts\Entity;
use Boyhagemann\Storage\Contracts\RecordRepository;

/**
 * Class Component
 * @package App
 * @method get( string $id, array $options = [])
 * @method find( array $query = [], array $options = [])
 * @method first( array $query = [], array $options = [])
 * @method insert( array $data, array $options = [])
 * @method update( string $id, array $data, array $options = [])
 * @method updateWhere( array $query, array $data, array $options = [])
 * @method upsert( string $id, array $data, array $options = [])
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