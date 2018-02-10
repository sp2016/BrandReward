<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
define('ROUTE_BASE', 'kernel/public');
$app->get(
    ROUTE_BASE . '/',
    function () use ($app) {
        return $app->welcome();
    }
);
$app->group(
    ['namespace' => 'App\Http\Controllers'],
    function ($app) {
        $app->post(ROUTE_BASE . '/welcome', 'WelcomeController@index');
        $app->get(ROUTE_BASE . '/welcome', 'WelcomeController@index');
        $app->post(ROUTE_BASE . '/stores','StoreController@getStores');

    }
);