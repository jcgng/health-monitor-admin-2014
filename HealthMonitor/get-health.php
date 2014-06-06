<?php
require_once('resources/config.php');	
require_once('resources/database.php');
require_once('resources/log.php');

$userName = (isset($_REQUEST['user'])?$_REQUEST['user']:NULL);
$password = (isset($_REQUEST['pass'])?encodeXT($_REQUEST['pass']):NULL);
$deviceId = (isset($_REQUEST['board'])?$_REQUEST['board']:NULL);
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
	if($deviceId!=NULL) {
		if(!$db->isUserBoard($user[0]['userName'],$deviceId)) {
			header("HTTP/1.1 403 Forbidden");
	    	exit;
		}
	}
}
$idPatients = (isset($_REQUEST['patient'])?$_REQUEST['patient']:NULL);
$bedNumber = (isset($_REQUEST['bedNumber'])?$_REQUEST['bedNumber']:NULL);
if($deviceId!=NULL || $idPatients!=NULL || $bedNumber!=NULL) {
	$res = $db->listLastHealth($deviceId,$idPatients,$bedNumber,/*orderby*/'dateTime',/*order*/'desc',/*limit*/3);
	if($res!=NULL) echo json_encode($res);
} else {
	header("HTTP/1.1 400 Bad Request");
	exit;
}