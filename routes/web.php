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
$app->get('component/{id}', 'ComponentController@show');
$app->post('component/action/component.create', 'ComponentController@create');
$app->get('component/{id}/build/{node}', 'ComponentController@build');
