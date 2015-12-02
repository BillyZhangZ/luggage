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
 
$app->get('/gps/:userId', 'getGps'); 
$app->get('/gps/:userId/:start/:end', 'getGpsBetween');
$app->post('/gps', 'addGps');

$app->post('/test', 'addTest');
$app->get('/test/:a', function($a){
	echo '{"/test/:a"}';
});
$app->get('/test/:a/:b', function($a, $b){
	echo '{"/test/:a/:b"}';
});
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




function getGps($userId) {
	$sql = "SELECT * FROM gps WHERE userId=:userId";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("userId", $userId);
		$stmt->execute();
		//while($gps=$stmt->fetch());
		$gpsAll = $stmt->fetchAll(PDO::FETCH_OBJ);
	        $gps = array_pop($gpsAll);		
		$db = null;
		echo json_encode($gps);
		//echo '{"gps": ' . json_encode($gps) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getGpsBetween($userId, $start, $end) {
	$sql = "SELECT * FROM gps WHERE userId=:userId and timeStamp between :start and :end";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("userId", $userId);
		$stmt->bindParam("start", $start);
		$stmt->bindParam("end", $end);
		$stmt->execute();
		$gps = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"gps": ' . json_encode($gps) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function addGps() {
	error_log('\naddGps\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$gps = json_decode($request->getBody());
	$sql = "INSERT INTO gps (userId, id, timeStamp, latitude, longtitude, altitude, hAccuracy, vAccuracy, speed) VALUES (:userId, :id, :timeStamp, :latitude, :longtitude, :altitude, :hAccuracy, :vAccuracy, :speed)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("userId", $gps->userId);
		$stmt->bindParam("id", $gps->id);
		$stmt->bindParam("timeStamp", $gps->timeStamp);
		$stmt->bindParam("latitude", $gps->latitude);
		$stmt->bindParam("longtitude", $gps->longtitude);
		$stmt->bindParam("altitude", $gps->altitude);
		$stmt->bindParam("hAccuracy", $gps->hAccuracy);
		$stmt->bindParam("vAccuracy", $gps->vAccuracy);
		$stmt->bindParam("speed", $gps->speed);
		$stmt->execute();
		$gps->id = $db->lastInsertId();
		$db = null;
		echo json_encode($gps); 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function addTest() {
	error_log('\naddTest\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$test = json_decode($request->getBody());
	$sql = "INSERT INTO test VALUES (:test)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("test", $test->test);
		$stmt->execute();
		$db = null;
		echo json_encode($test); 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
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
