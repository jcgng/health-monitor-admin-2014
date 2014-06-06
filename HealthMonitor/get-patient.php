<?php
require_once('resources/config.php');	
require_once('resources/database.php');
require_once('resources/log.php');

$userName = (isset($_REQUEST['user'])?$_REQUEST['user']:NULL);
$password = (isset($_REQUEST['pass'])?encodeXT($_REQUEST['pass']):NULL);
$idPatients = (isset($_REQUEST['patient'])?$_REQUEST['patient']:NULL);
if(($userName==NULL) || ($password==NULL)) {
	header("HTTP/1.1 403 Forbidden");
    exit;
}

$db = new Database(HOST,DB,USER,PASS);
// check if it is admin
if(!$db->isAdmin($userName, $password)) {
	$user = $db->getUser($userName,$password);
	if(count($user)==0) {
		header("HTTP/1.1 403 Forbidden");
	    exit;
	}
	$boards = $db->listUserBoards($user[0]['idUsers']);
	$patients = array();
	foreach($boards as $board) {
		$patients = array_merge($patients,$db->listPatients($idPatients,$board['deviceId']));		
	}
} else {
	$patients = $db->listPatients($idPatients);
}
foreach($patients as $key => $patient) {
	$medications = $db->listPatientMedications($patient['idPatients']);
	$patients[$key]['medications'] = $medications;
}
if($patients!=NULL) echo json_encode($patients);
 