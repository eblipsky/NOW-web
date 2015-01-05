<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');

Route::get('hpc/test', 'TestController@index');

Route::get('hpc/overview', 'OverviewController@index');
Route::get('hpc/pipeline/{pipeline}/overview', 'OverviewController@overview');

Route::get('hpc/log/{logid}', 'LogController@index');

Route::get('hpc/fm', 'FileManagerController@index');

Route::get('hpc/settings', 'SettingsController@index');
Route::post('hpc/settings', 'SettingsController@save');

Route::get('hpc/batches', 'HPCBatchController@index');
Route::get('hpc/batches/create', 'HPCBatchController@create');
Route::post('hpc/batches', 'HPCBatchController@store');
Route::get('hpc/batches/{name}/edit', 'HPCBatchController@edit');
Route::get('hpc/batches/{name}/start', 'HPCBatchController@start');
Route::put('hpc/batches/{name}', 'HPCBatchController@update');
Route::get('hpc/batches/{name}', 'HPCBatchController@show');
Route::get('hpc/batches/{name}/progress', 'HPCBatchController@progress');

Route::get('sig', 'SigController@get');
Route::post('sig', 'SigController@post');

Route::get('hpc/monitor', 'HPCController@monitor');

Route::get('hpc/newfiles', 'FilesController@newfiles');
Route::get('hpc/files', 'FilesController@allfiles');
Route::get('hpc/files/import', 'FilesController@import');
Route::get('hpc/file/{fq}', 'FilesController@file');
Route::post('hpc/files/priority', 'FilesController@setPriority');

Route::get('hpc/allfiles', 'HPCController@allfiles');
Route::post('hpc/startfiles', 'PipelineController@startFiles');
Route::post('hpc/pipeline/{pipeline}/clear', 'PipelineController@clear');

Route::get('hpc/templatecmds', 'TemplateCmdController@index');
Route::post('hpc/templatecmds', 'TemplateCmdController@store');

Route::get('hpc/vars', 'GVarsController@index');
Route::post('hpc/vars', 'GVarsController@store');
Route::put('hpc/vars/{name}', 'GVarsController@update');
Route::delete('hpc/vars/{name}', 'GVarsController@destroy');

Route::post('hpc/filevars/{fq}', 'FileVarsController@store');
Route::put('hpc/filevars/{fq}/{name}', 'FileVarsController@update');
Route::delete('hpc/filevars/{fq}/{name}', 'FileVarsController@destroy');

Route::get('hpc/nodes', 'NodeController@view');
Route::post('hpc/nodes/{node}/pipeline', 'NodeController@setPipeline');

Route::post('hpc/pipeline', 'PipelineController@create');
Route::delete('hpc/pipeline/{pipeline}', 'PipelineController@delete');

Route::get('hpc/pipeline/{pipeline}', 'PipelineController@view');
Route::post('hpc/pipeline/{pipeline}/queue', 'QueueController@create');
Route::post('hpc/movefiles', 'QueueController@movefiles');
Route::delete('hpc/pipeline/{pipeline}/queue/{queue}', 'QueueController@delete');

Route::get('hpc/pipeline/{pipeline}/vars', 'PipelineController@pipeline_vars');
Route::post('hpc/pipeline/{pipeline}/vars', 'PipelineController@updateVars');

Route::get('hpc/pipeline/{pipeline}/queues', 'QueueController@queues');
Route::get('hpc/pipeline/{pipeline}/queue/{queue}', 'QueueController@queue');
Route::post('hpc/pipeline/{pipeline}/queue/{queue}', 'QueueController@update');
