<?php

use Illuminate\Support\Collection;
use Helpers\DataBuilder;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


/** @var PDO $pdo */
$pdo = $app->make('pdo');

/** @var \Boyhagemann\Storage\Contracts\EntityRepository $entities */
$entities = $app->make('entities');

/** @var \Boyhagemann\Storage\Contracts\Record $entities */
$records = $app->make('records');




$app->get('/', function () use ($app) {
    return $app->version();
});

/**
 * Entities
 */
$app->get('/resource', function () use ($app, $entities) {
    return $entities->find();
});
$app->get('/resource/{id}', function ($id) use ($app, $entities) {
    return $entities->get($id);
});


/**
 * Records
 */
$app->get('/resource/{resource}/data', function ($resource) use ($app, $entities, $records) {
    $entity = $entities->get($resource);
    return $records->find($entity);
});
$app->get('/resource/{resource}/data/{record}', function ($resource, $record) use ($app, $entities, $records) {
    $entity = $entities->get($resource);
    return $records->get($entity, $record);
});

$app->get('/resource/{resource}/build/{record}/{node}', function ($resource, $record, $node) use ($app, $entities, $records) {

    $entity = $entities->get($resource);
    $component = $records->get($entity, $record);

    // Find the dependencies
    $dependencies = $component['uses']
        ? $records->find($entity, [
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
});
