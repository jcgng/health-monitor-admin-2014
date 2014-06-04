<?php
require_once('resources/config.php');
require_once('resources/database.php');
require_once('resources/log.php');

$userName = (isset($_REQUEST['user'])?$_REQUEST['user']:NULL);
$password = (isset($_REQUEST['pass'])?encodeXT($_REQUEST['pass']):NULL);
if(($userName==NULL) || ($password==NULL)) { 
	header("HTTP/1.1 403 Forbidden");
    exit;
}

$db = new Database(HOST,DB,USER,PASS);
$user = $db->getUser($userName,$password);
if(count($user)==0) { 
	header("HTTP/1.1 403 Forbidden");
    exit;
}

$deviceId = (isset($_REQUEST['board'])?$_REQUEST['board']:NULL);
if($deviceId!=NULL)
if(!$db->isUserBoard($user[0]['userName'],$deviceId)) { 
	header("HTTP/1.1 403 Forbidden");
    exit;
}
