<?php

use App\Models\BankDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UtilsController;

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return response()->json(['VoguePay Test']);
});

$router->group(
    ['prefix' => 'api/v1'], 
    function() use ($router) {

    /**
     * Auth Routes
     */
    $router->post('/auth/login', 'Auth\LoginController@store');
    $router->post('/auth/signup', 'Auth\SignupController@store');

    $router->group(
        ['middleware' => 'jwt.auth'], 
        function() use ($router) {

            // Transactions endpoints
            $router->post('transactions/create', 'TransactionsController@create');
            $router->get('transactions/history', 'TransactionsController@history');
            $router->get('transactions/single', 'TransactionsController@single');
            $router->get('transactions/search', 'TransactionsController@search');

            $router->post('transactions/verify', 'TransactionsController@verify');
        }
    );
});