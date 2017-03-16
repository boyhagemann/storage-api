<?php

use Illuminate\Support\Collection;
use Helpers\DataBuilder;
use Helpers\ActionHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

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

$app->get('/', function () use ($app) {
    return $app->version();
});

/**
 * Components
 */
$app->get('component', 'ComponentController@index');
$app->get('component/{component}', 'ComponentController@show');
$app->post('component', 'ComponentController@store');
$app->put('component/{component}', 'ComponentController@store');
$app->delete('component/{component}', 'ComponentController@store');

/**
 * Nodes
 */
$app->get('component/{component}/node', 'NodeController@index');
$app->get('component/{component}/node/{node}', 'NodeController@show');
$app->post('component/{component}/node', 'NodeController@store');
$app->put('component/{component}/node/{node}', 'NodeController@update');
$app->delete('component/{component}/node/{node}', 'NodeController@destroy');
$app->get('component/{component}/node/{node}/build', 'NodeController@build');
