<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Psr\Http\Message\ServerRequestInterface;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::mixin(new \Laravel\Ui\AuthRouteMethods());
Route::auth(['verify' => true]);

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('curriculum')->group(function(){
// Paths
    Route::get('/', 'Curriculum\PathsController@getPaths');
    Route::get('paths', 'Curriculum\PathsController@getPaths');
    Route::post('paths', 'Curriculum\PathsController@postPaths');
    Route::post('path/{pathId?}', 'Curriculum\PathsController@createOrUpdatePath');
    Route::any('path/view/{pathId?}', 'Curriculum\PathsController@viewPath');
    Route::get('path/missing/{type}', 'Curriculum\PathsController@missingPath');
// Prompts
    Route::get('prompts', function () {
        return redirect()->action('Curriculum\PathsController@missingPath', [ 'type' => 'prompts' ]);
    });
    Route::get('prompts/{pathId}', 'Curriculum\PromptsController@getPrompts');
    Route::post('prompts/{pathId}', 'Curriculum\PromptsController@postPrompts');
    Route::any('prompts/view/{pathId}', 'Curriculum\PromptsController@viewPrompts');
    Route::post('prompt/create/{pathId}', 'Curriculum\PromptsController@createPrompt');
    Route::post('prompt/edit/{pathId}/{promptId}', 'Curriculum\PromptsController@editPrompt');
// Segments
    Route::get('segments', function () {
        return redirect()->action('Curriculum\PathsController@missingPath', [ 'type' => 'segments' ]);
    });
    Route::get('segments/{promptId}', 'Curriculum\PromptSegmentsController@getSegments');
    Route::post('segments/create/{promptId}', 'Curriculum\PromptSegmentsController@createSegment');
    Route::post('segments/edit/{promptId}/{segmentId}', 'Curriculum\PromptSegmentsController@editSegment');
// Questions
    Route::get('questions', function () {
        return redirect()->action('Curriculum\PathsController@missingPath', [ 'type' => 'questions' ]);
    });
    Route::get('questions/{pathId}', 'Curriculum\SamplingQuestionsController@getSamplingQuestions');
    Route::post('questions/create/{pathId}', 'Curriculum\SamplingQuestionsController@createSamplingQuestion');
    Route::post('questions/edit/{questionId}', 'Curriculum\SamplingQuestionsController@editSamplingQuestion');
// Users
    Route::get('editors', function () {
        return redirect()->action('Curriculum\PathsController@missingPath', [ 'type' => 'editors' ]);
    });
    Route::get('editors/{pathId}', 'Curriculum\EditorsController@getEditors');
    Route::post('editors/{pathId}', 'Curriculum\EditorsController@postEditors');
    Route::get('invite/{pathId}', 'Curriculum\EditorsController@getInvite');
    Route::post('invite/{pathId}', 'Curriculum\EditorsController@postInvite');
});

Route::get('/home', 'HomeController@index')->name('home');
