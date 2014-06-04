<?php
if((session_id() == '') || session_status() == PHP_SESSION_NONE) {
	session_start();
}

define("ABSPATH","c:/xampp/htdocs/HealthMonitor/");
/** database **/
define("HOST","localhost");
define("DB","HealthMonitor");
define("USER","root");
define("PASS","");
