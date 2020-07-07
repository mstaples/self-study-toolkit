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
    return redirect()->action('Curriculum\PathsController@getPaths');
});

Route::prefix('curriculum')->group(function(){
// Paths
    Route::get('/', 'Curriculum\PathsController@getPaths');
    Route::get('paths', 'Curriculum\PathsController@getPaths');
    Route::post('paths', 'Curriculum\PathsController@postPaths');
    Route::get('path/demo/{pathId}', 'Curriculum\PathsController@getDemoPath');
    Route::post('path/demo/{pathId}', 'Curriculum\PathsController@postDemoPath');
    Route::post('path/{pathId?}', 'Curriculum\PathsController@createOrUpdatePath');
    Route::any('path/view/{pathId?}', 'Curriculum\PathsController@viewPath');
    Route::get('path/missing/{type}', 'Curriculum\PathsController@missingPath');
// Prompts
    Route::get('prompts/{pathId?}', 'Curriculum\PromptsController@getPrompts');
    Route::post('prompts/{pathId}', 'Curriculum\PromptsController@postPrompts');
    Route::any('prompts/view/{pathId}', 'Curriculum\PromptsController@viewPrompts');

    Route::any('prompt/demo/{promptId}', 'Curriculum\PromptController@anyDemoPrompt');
    Route::post('prompt/create/{pathId}', 'Curriculum\PromptController@createPrompt');
    Route::post('prompt/edit/{pathId}/{promptId}', 'Curriculum\PromptController@editPrompt');
    Route::any('prompt/{pathId}/{promptId}', 'Curriculum\PromptController@postPrompt');
// Segments
    Route::get('segments', function () {
        return redirect()->action('Curriculum\PathsController@missingPath', [ 'type' => 'segments' ]);
    });
    Route::get('segments/{promptId}', 'Curriculum\PromptSegmentsController@getSegments');
    Route::post('segments/edit/{segmentId}/{index}', 'Curriculum\PromptSegmentsController@editSegment');
    Route::post('segments/new', 'Curriculum\PromptSegmentsController@newSegment');
    Route::post('segments/up/{segmentId}', 'Curriculum\PromptSegmentsController@upSegment');
    Route::any('segments/down/{segmentId}', 'Curriculum\PromptSegmentsController@downSegment');
    Route::post('segments/delete/{segmentId}', 'Curriculum\PromptSegmentsController@deleteSegment');
// Questions
    Route::get('knowledges', 'Curriculum\SamplingQuestionsController@getKnowledges');
    Route::post('knowledges', 'Curriculum\SamplingQuestionsController@postKnowledges');
    Route::post('knowledge/create', 'Curriculum\SamplingQuestionsController@createKnowledge');

    Route::get('questions/', 'Curriculum\SamplingQuestionsController@getKnowledges');
    Route::post('questions/', 'Curriculum\SamplingQuestionsController@postKnowledges');
    Route::get('questions/{knowledge?}', 'Curriculum\SamplingQuestionsController@getSamplingQuestions');
    Route::post('questions/select', 'Curriculum\SamplingQuestionsController@postSamplingQuestions');
    Route::post('questions/create', 'Curriculum\SamplingQuestionsController@createOrUpdateSamplingQuestion');
    Route::post('questions/edit/{questionId}', 'Curriculum\SamplingQuestionsController@createOrUpdateSamplingQuestion');
    // Questions answer options
    Route::post('options/create/{questionId?}', 'Curriculum\SamplingOptionsController@createSamplingOption');
    Route::post('options/edit/{questionId}/{optionId}', 'Curriculum\SamplingOptionsController@editSamplingOption');
    Route::post('options/delete/{questionId}/{optionId}', 'Curriculum\SamplingOptionsController@deleteSamplingOption');
    Route::post('options/all/{questionId}', 'Curriculum\SamplingOptionsController@allSamplingOptions');
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
