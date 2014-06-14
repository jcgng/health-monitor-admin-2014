<?php 	
require_once('resources/config.php');
require_once('resources/functions.php');
require_once('resources/log.php');
require_once('resources/database.php');
require_once('resources/generic-website.php');

/** PERMISSIONS **/
try {
	$db = new Database(HOST,DB,USER,PASS);
	if(!$db->isAdmin($_SESSION['username'],$_SESSION['passhash']) || ($_SESSION['deathstamp'] <= time()) || (isset($_REQUEST['action']) && ($_REQUEST['action']=='logout'))) {
		$errors[] = printError(__FILE__,"Session ended!");
		session_unset();
		header('Location: /HealthMonitor/admin-login.php',true,302);
	}
} catch(Exception $ex) {
	$errors[] = printError(__FILE__,$ex->getMessage());
	return 0;
}

printInfo(__FILE__,"Loading Page...");

/** CONSTANTS **/
$title = 'Health Monitor Administrator';

/** Tabs **/
$currentPageUrl = explode('?',Page::currentPageURL());
$default = 'boards';
$settings = $default;
if(isset($_REQUEST['settings'])) {
	$settings = $_REQUEST['settings'];
} else if(isset($_POST['settings'])) {
	$settings = $_POST['settings'];
}
$tabs = array(
	'boards' 		=> array('label' => 'Boards',   	'url' => $currentPageUrl[0].'?settings=boards'	 	),
	'users'  		=> array('label' => 'Users',    	'url' => $currentPageUrl[0].'?settings=users'	 	),
	'pharmacy'  	=> array('label' => 'Pharmacy', 	'url' => $currentPageUrl[0].'?settings=pharmacy' 	),
	'patients'  	=> array('label' => 'Patients', 	'url' => $currentPageUrl[0].'?settings=patients' 	),
	'tests'  		=> array('label' => 'Tests',    	'url' => $currentPageUrl[0].'?settings=tests'	 	));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo $title; ?></title>
  <!-- CSS -->
  <link rel="stylesheet" href="css/my-css.css" type="text/css" media="screen" />
  <script type="text/javascript" src="javascript/my-scripts.js"></script>
</head>
<body>
<!-- HTML -->
<div class="plugin-banner"></div>
<div class="wrap">
<img class="icon32" src="images/splashscreen.png"/>
<h2><?php echo $title; ?></h2><a href="<?php echo $currentPageUrl[0]; ?>?action=logout" class="logout" >Logout</a>
<h3 class="nav-tab-wrapper">
<?php
	Page::separatorBar($settings, $default, $tabs);
?>
</h3>
<?php	
	$pageName=$tabs[$settings]['label'];
	switch($settings) {
		case 'users':
			require_once('admin-users.php');
			break;
		case 'pharmacy':
			require_once('admin-pharmacy.php');
			break;
		case 'patients':
			require_once('admin-patients.php');
			break;
		case 'tests':
			require_once('admin-tests.php');
			break;
		case 'boards':
		default:
			require_once('admin-coordinators.php');
			require_once('admin-boards.php');
			break;
	}
?>
</div>
<?php
printInfo(__FILE__,"Page Loaded!");
?>
</body>
</html>
