<?php

Route::group(['as' => 'tasks::'], function () {

	Route::get('/', ['as' => 'main', 'uses' => 'TasksController@listItems']);
	Route::get('/tasks', ['as' => 'list', 'uses' => 'TasksController@listItems']);
	
	Route::get('/tasks/add', ['as' => 'add', 'uses' => 'TasksController@editItem']);
	Route::post('/tasks/add', ['as' => 'add_post', 'uses' => 'TasksController@addItem']);
	
	Route::get('/tasks/edit/{id}', ['as' => 'edit', 'uses' => 'TasksController@editItem'])->where(['id' => '[0-9]+']);
	
	Route::post('/tasks/edit/{id}', ['as' => 'save', 'uses' => 'TasksController@saveItem'])->where(['id' => '[0-9]+']);
	
	Route::get('/tasks/delete/{id}', ['as' => 'delete', 'uses' => 'TasksController@deleteItem'])->where(['id' => '[0-9]+']);
	
});

Route::group(['as' => 'groups::'], function () {

	Route::get('/groups', ['as' => 'list', 'uses' => 'GroupsController@listItems']);
	
	Route::get('/groups/add', ['as' => 'add', 'uses' => 'GroupsController@editItem']);
	Route::post('/groups/add', ['as' => 'add_post', 'uses' => 'GroupsController@addItem']);

	Route::get('/groups/edit/{id}', ['as' => 'edit', 'uses' => 'GroupsController@editItem'])->where(['id' => '[0-9]+']);
	
	Route::post('/groups/edit/{id}', ['as' => 'save', 'uses' => 'GroupsController@saveItem'])->where(['id' => '[0-9]+']);
	
	Route::get('/groups/delete/{id}', ['as' => 'delete', 'uses' => 'GroupsController@deleteItem'])->where(['id' => '[0-9]+']);
	
});


Route::group(['as' => 'log::'], function () {

	Route::get('/log', ['as' => 'index', 'uses' => 'LogController@index']);
	
});


