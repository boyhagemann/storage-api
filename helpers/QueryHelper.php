<?php namespace Helpers;

use Illuminate\Support\Collection;

class QueryHelper
{
    /**
     * @param array $filters
     * @return Collection
     */
    public static function transform(Array $filters)
    {
        return Collection::make($filters)
            ->map(function(Array $filter) {
                return $filter + [
                    'operator' => '=',
                    'cast' => 'string'
                ];
            })
            ->map(function(Array $filter) {
                $filter['value'] = static::cast($filter);
                return $filter;
            })
            ->map(function(Array $filter) {
               return [$filter['field'], $filter['operator'], $filter['value']];
            })
            ->toArray();
    }

    /**
     * @param array $filter
     * @return bool|mixed
     */
    public static function cast(Array $filter)
    {
        $value = $filter['value'];
        $type = $filter['cast'];

        switch($type) {

            case 'list':
                return explode(',', $value);

            case 'boolean':
            case 'bool':

                switch($value) {

                    case 'false':
                        return false;

                    case 'true':
                        return true;

                    default:
                        return (bool) $value;
                }
                break;
        }

        return $value;
    }

}
