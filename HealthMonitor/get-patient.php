<?php
require_once('resources/config.php');	
require_once('resources/database.php');
require_once('resources/log.php');

$userName = (isset($_REQUEST['user'])?$_REQUEST['user']:NULL);
$password = (isset($_REQUEST['pass'])?encodeXT($_REQUEST['pass']):NULL);
if(($userName==NULL) || ($password==NULL)) die('Need user, password!');

$db = new Database(HOST,DB,USER,PASS);
$user = $db->getUser($userName,$password);
if(count($user)==0) die('Invalid user!');

$idPatients = (isset($_REQUEST['patient'])?$_REQUEST['patient']:NULL);
$boards = $db->listUserBoards($user[0]['idUsers']);
$patients = array();
foreach($boards as $board) {
	$patients = array_merge($patients,$db->listPatients($idPatients,$board['deviceId']));		
}
foreach($patients as $key => $patient) {
	$medications = $db->listPatientMedications($patient['idPatients']);
	$patients[$key]['medications'] = $medications;
}
if($patients!=NULL) echo json_encode($patients);
 