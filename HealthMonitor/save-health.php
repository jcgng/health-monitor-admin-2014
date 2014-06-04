<?php	
// TODO: Respond to JSON POST and save it to database

// require_once('resources/config.php');
// require_once('resources/database.php');
// require_once('resources/log.php');

// printInfo(__FILE__,'Save health values...');

// $userName = (isset($_REQUEST['user'])?$_REQUEST['user']:NULL);
// $password = (isset($_REQUEST['pass'])?encodeXT($_REQUEST['pass']):NULL);
// $deviceId = (isset($_REQUEST['board'])?$_REQUEST['board']:NULL);
// if(($userName==NULL) || ($password==NULL) || ($deviceId==NULL)) die('Need user, password and device ID!');

// $db = new Database(HOST,DB,USER,PASS);
// $user = $db->getUser($userName,$password);
// if(count($user)==0) die('Invalid user!');
// if(!$db->isUserBoard($user[0]['userName'],$deviceId)) die('Not user board!');

// $bpm = (isset($_REQUEST['bpm'])?$_REQUEST['bpm']:NULL);
// $temperature = (isset($_REQUEST['temp'])?$_REQUEST['temp']:NULL); 
// $balance = (isset($_REQUEST['blc'])?$_REQUEST['blc']:NULL);
// if(($bpm==NULL) || ($temperature==NULL) || ($balance==NULL)) die('Need all values to register!');

// $boards = $db->listBoards(/*idBoards*/NULL,$deviceId);
// $db->saveHealthValues($bpm, $temperature, $balance, $boards[0]['idBoards']);

// printInfo(__FILE__,"Values saved: $bpm, $temperature, $balance, " + $boards[0]['idBoards']);