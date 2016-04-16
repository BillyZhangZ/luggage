<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/users', 'getUsers');
$app->get('/users/:id','getUser');
$app->get('/users/search/:query', 'findByName');
$app->post('/users', 'addUser');
$app->put('/users/:id', 'updateUser');
$app->delete('/users/:id',	'deleteUser');

$app->post('/login', 'login');
$app->post('/register', 'register');

$app->post('/bonddevice', 'bondDevice');
$app->post('/unbonddevice', 'unbondDevice');

$app->get('/','getAll');
 
$app->get('/gps/:userId', 'getGps'); 
$app->get('/gps/:userId/:n', 'getLatestNGps'); 
$app->get('/gps/:userId/:start/:end', 'getGpsBetween');
$app->post('/gps', 'addGps');

$app->get('/cellbase/:deviceId', 'getCellBase'); 
$app->post('/cellbase', 'addCellBase');

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

function checkUserExist($email)
{
	error_log('\ncheckuser\n', 3, '/var/tmp/php.log');
	$sql = "SELECT * FROM user WHERE email=:email";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("email", $email);
		$stmt->execute();
		$user = $stmt->fetchObject();  
		$db = null;
		if(!empty($user)) return 1;
		else return 0;
	
	} catch(PDOException $e) {
		return 2;
	}
	return 0;
}

function register() {
	error_log('\nregister\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$user = json_decode($request->getBody());
	if(checkUserExist($user->email) != 0) 
	{
        	echo '{"id":"0"}'; 
		return;
	}


	$sql = "INSERT INTO user (id, name, email, password, phoneNumber, sex, debug) VALUES (:id, :name, :email, :password, :phoneNumber,  :sex, :debug)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $user->id);
		$stmt->bindParam("name", $user->name);
		$stmt->bindParam("email", $user->email);
		$stmt->bindParam("password", $user->password);
		$stmt->bindParam("phoneNumber", $user->phoneNumber);
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

function login() {
	error_log('\nlogin\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$param = json_decode($request->getBody());
	$sql = "SELECT * FROM user WHERE email=:email and password=:password";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("email", $param->email);
		$stmt->bindParam("password", $param->password);
		$stmt->execute();
		$user = $stmt->fetchObject();  
		$db = null;
		echo json_encode($user); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function bondDevice() {
	error_log('\nbonddevice\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$param = json_decode($request->getBody());
	$sql = "UPDATE user SET deviceId=:deviceId, deviceSim=:deviceSim where id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $param->userId);
		$stmt->bindParam("deviceId", $param->deviceId);
		$stmt->bindParam("deviceSim", $param->deviceSim);
		$stmt->execute();
		$db = null;
		echo '{"ok":1}'; 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function unbondDevice() {
	error_log('\nunbonddevice\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$param = json_decode($request->getBody());
	$sql = "UPDATE user SET deviceId=:deviceId, deviceSim=:deviceSim where id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $param->userId);
		$stmt->bindParam("deviceId", 0);
		$stmt->bindParam("deviceSim", 0);
		$stmt->execute();
		$db = null;
		echo '{"ok":1}'; 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function getGps($userId) {
	$sql = "SELECT * FROM user WHERE id=:userId";
	$sql1 = "SELECT * FROM gps WHERE deviceId=:deviceId";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("userId", $userId);
		$stmt->execute();
		$user = $stmt->fetchObject();  

		$stmt1 = $db->prepare($sql1);
		$stmt1->bindParam("deviceId",$user->deviceId);
		$stmt1->execute();
		$gpsAll = $stmt1->fetchAll(PDO::FETCH_OBJ);
	        $gps = array_pop($gpsAll);		
		$db = null;
		echo json_encode($gps);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function getGpsBetween($userId, $start, $end) {
	$sql = "SELECT * FROM gps WHERE deviceId=:deviceId and timeStamp between :start and :end";
	$sql1 = "SELECT * FROM user WHERE id=:userId";
	try {
		$db = getConnection();
		$stmt1 = $db->prepare($sql);  
		$stmt1->bindParam("userId", $userId);
		$stmt1->execute();
		$user = $stmt->fetchObject();  

		$stmt = $db->prepare($sql);  
		$stmt->bindParam("deviceId", $user->deviceId);
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

function getLatestNGps($userId, $n)
{

	$sql = "SELECT * FROM gps WHERE deviceId=:deviceId";
	$sql1 = "SELECT * FROM user WHERE id=:userId";
	try {
		$db = getConnection();
		$stmt1 = $db->prepare($sql1);  
		$stmt1->bindParam("userId", $userId);
		$stmt1->execute();
		$user = $stmt1->fetchObject();  

		$stmt = $db->prepare($sql);  
		$stmt->bindParam("deviceId", $user->deviceId);
		$stmt->execute();
		$allGps = $stmt->fetchAll(PDO::FETCH_OBJ);
		$gps = array_slice($allGps, count($allGps) - $n, count($allGps) - 1);
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
	$sql = "INSERT INTO gps (userId, id,deviceId, timeStamp, latitude, longtitude, altitude, hAccuracy, vAccuracy, speed) VALUES (:userId, :id, :deviceId, :timeStamp, :latitude, :longtitude, :altitude, :hAccuracy, :vAccuracy, :speed)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("userId", $gps->userId);
		$stmt->bindParam("id", $gps->id);
		$stmt->bindParam("deviceId", $gps->deviceId);
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


function getCellBase($deviceId) {
	$sql = "SELECT * FROM cellbase WHERE deviceId=:deviceId";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("deviceId", $deviceId);
		$stmt->execute();
		//while($gps=$stmt->fetch());
		$cellBaseAll = $stmt->fetchAll(PDO::FETCH_OBJ);
	        $cellBase = array_pop($cellBaseAll);		
		$db = null;
		echo json_encode($cellBase);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function addCellBase() {
	error_log('\naddCellBase\n', 3, '/var/tmp/php.log');
	
	$request = Slim::getInstance()->request();
	$cellbase = json_decode($request->getBody());
	$sql = "INSERT INTO cellbase (userId, id, deviceId, timeStamp, mcc, mnc, lac, cid) VALUES (:userId, :id,:deviceId, :timeStamp, :mcc, :mnc, :lac, :cid)";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("userId", $cellbase->userId);
		$stmt->bindParam("id", $cellbase->id);
		$stmt->bindParam("deviceId", $cellbase->deviceId);
		$stmt->bindParam("timeStamp", $cellbase->timeStamp);
		$stmt->bindParam("mcc", $cellbase->mcc);
		$stmt->bindParam("mnc", $cellbase->mnc);
		$stmt->bindParam("lac", $cellbase->lac);
		$stmt->bindParam("cid", $cellbase->cid);
		$stmt->execute();
		$cellbase->id = $db->lastInsertId();
		$db = null;
		echo json_encode($cellbase); 
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
