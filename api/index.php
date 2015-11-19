<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/users', 'getUsers');
$app->get('/users/:id','getUser');
$app->get('/users/search/:query', 'findByName');
$app->post('/users', 'addUser');
$app->put('/users/:id', 'updateUser');
$app->delete('/users/:id',	'deleteUser');

$app->get('/','getAll');


$app->run();

function getUsers() {
	$sql = "select * FROM user ORDER BY name";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"users": ' . json_encode($users) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getAll() {
	echo '{"this is a test api call"}';
}

function getUser($id) {
	$sql = "SELECT * FROM user WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$user = $stmt->fetchObject();  
		$db = null;
		echo json_encode($user); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function addUser() {
	error_log('\naddUser\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$user = json_decode($request->getBody());
	$sql = "INSERT INTO user (id, name, sex, debug) VALUES (:id, :name, :sex, :debug)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $user->id);
		$stmt->bindParam("name", $user->name);
		$stmt->bindParam("sex", $user->sex);
		$stmt->bindParam("debug", $user->debug);
		$stmt->execute();
		$user->id = $db->lastInsertId();
		$db = null;
		echo json_encode($user); 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function updateUser($id) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$user = json_decode($body);
	$sql = "UPDATE user SET id=:id, name=:name, sex=:sex, debug=:debug";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $user->id);
		$stmt->bindParam("name", $user->name);
		$stmt->bindParam("sex", $user->sex);
		$stmt->bindParam("debug", $user->debug);
		$stmt->execute();
		$db = null;
		echo json_encode($user); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function deleteUser($id) {
	$sql = "DELETE FROM user WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function findByName($query) {
	$sql = "SELECT * FROM user WHERE UPPER(name) LIKE :query ORDER BY name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";  
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$user = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"user": ' . json_encode($user) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="123456";
	$dbname="draw_bar_box";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>
