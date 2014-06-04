<?php
	require_once('resources/config.php');
	require_once('resources/functions.php');
	require_once('resources/log.php');
	require_once('resources/database.php');
	require_once('resources/generic-website.php');

	// 	if (!current_user_can('admin'))
	// 		die('You do not have sufficient permissions to access this page.');
	
	printInfo(__FILE__,"Loading Page...");
	
	/** CONSTANTS **/
	$title = 'Health Monitor Administrator';
	
	/** Tabs **/
	$currentPageUrl = explode('?',Page::currentPageURL());
	
	class AdminsPage extends Page {
		public static function startSession($userName,$password) {
			session_regenerate_id();
			session_unset();
			// save session
			$_SESSION['username'] = $userName;
			$_SESSION['passhash'] = $password;
			$_SESSION['deathstamp'] = time() + 3600;
			header('Location: /HealthMonitor/admin-website.php',true,302);
		}
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'admin-login':
					$userName = $_POST['userName'];
					if(is_empty($userName)) {
						$errors[] = printError(__FILE__,'Missing admin name.');
						return -1;
					}
					$password = $_POST['password'];
					if(is_empty($password)) {
						$errors[] = printError(__FILE__,'Missing admin password.');
						return -2;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->isAdmin($userName,encodeXT($password))) {
							$errors[] = printError(__FILE__,'Error invalid username or password.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					AdminsPage::startSession($userName,encodeXT($password));
					break;
			}
			return 1;
		}
	}
	$res = 0;
	$errors = array();
	$messages = array();
	if(isset($_REQUEST['action'])) {
		$res = AdminsPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title></title>
  <!-- CSS -->
  <link rel="stylesheet" href="css/my-css.css" type="text/css" media="screen" />
  <script type="text/javascript" src="javascript/my-scripts.js"></script>
</head>
<body>
<!-- HTML -->
<div class="plugin-banner"></div>
<div class="wrap">
<!-- <img class="icon32" src="images/"/> -->
<h2><?php echo $title; ?> </h2>
<?php 
	$pageName = 'Login';
	$forms = array();
	// register admin
	$elements = array(
			array('User' => '<input type="text" name="userName" id="userName" value="'.(($res<=0 && isset($_POST['userName']))?$_POST['userName']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />'),
			array('Password' => '<input type="password" name="password" id="password" value="'.(($res<=0 && isset($_POST['password']))?$_POST['password']:'').'" '.($res==-2?'style="border-color:#C67171"':'').' />'));
	$formAdmins = new Form($currentPageUrl[0].'?settings=login',$pageName,'admin-login',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Login');
	$forms[] = $formAdmins;
	// print admins page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new AdminsPage($pageName,$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);
?>
</div>
<?php
printInfo(__FILE__,"Page Loaded!");
?>
</body>
</html>
