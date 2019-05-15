<?php

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\BoardController;

require 'vendor/autoload.php';

session_start();

$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => true,
	]
]);

$container = $app->getContainer();

$container['db'] = function () {
	$db = new PDO('mysql:host=localhost;dbname=ymstest', 'root', '1234');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	return $db;
};

$app->group('/user', function () {
	$this->post('/join', UserController::class . ':join');
	$this->post('/modify', UserController::class . ':modify');
	$this->post('/login', UserController::class . ':login');
	$this->post('/logout', UserController::class . ':logout');
});

$app->group('/board', function () {
	$this->get('/page/{no}', BoardController::class . ':page');
	$this->get('/view/{no}', BoardController::class . ':view');
	$this->post('/write', BoardController::class . ':write');
	$this->post('/modify/{no}', BoardController::class . ':modify');
	$this->delete('/delete/{no}', BoardController::class . ':delete');
});


$app->run();

