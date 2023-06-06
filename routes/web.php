<?php

use Illuminate\Support\Facades\Route;
use Statamic\Contracts\Taxonomies\Term as TaxonomiesTerm;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create-taxonomy', function() {
    
    $term = Term::make()->taxonomy('tags')->slug('my-term')->inDefaultLocale();    
    $term->save();


    die('ok');
             
});