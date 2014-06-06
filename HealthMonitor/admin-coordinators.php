<?php
	$currentPageUrl = explode('?',Page::currentPageURL());

	class CoordinatorsPage extends Page {
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'register-coordinator':
					$deviceId = $_POST['deviceId'];
					if(is_empty($deviceId)) {
						$errors[] = printError(__FILE__,'Missing coordinator device ID.');
						return -1;
					}
					$poolingTime = $_POST['poolingTime'];
					if(is_empty($poolingTime) || !is_numeric($poolingTime)) {
						$errors[] = printError(__FILE__,'Missing or invalid coordinator pooling time.');
						return -2;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->registerCoordinator($deviceId,$poolingTime)) {
							$errors[] = printError(__FILE__,'Error registering coordinator.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Coordinator successfully registered.');
					break;
				case 'delete-coordinator':
					$deviceId = $_REQUEST['coordinator'];
					if(is_empty($deviceId)) {
						$errors[] = printError(__FILE__,'Missing device ID.');
						return 0;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->deleteCoordinator($deviceId)) {
							$errors[] = printError(__FILE__,'Error deleting coordinator.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Coordinator successfully deleted.');
					break;
			}
			return 1;
		}
	}
	class CoordinatorsList extends ListTable {
		function getColumns() {
			return array('deviceId' => 'Device', 'registerTimestamp' => 'Timestamp', 'poolingTime' => 'Pooling', 'status' => 'Status');
		}
		function defaultColumns($item,$column_name) {
			switch($column_name) {
				case 'deviceId':
					$currentPageUrl = explode('?',Page::currentPageURL());
					$settings = (isset($_REQUEST['settings'])?$_REQUEST['settings']:'');
					return sprintf('%1$s</br><a href="%2$s?settings=%3$s&action=delete-coordinator&coordinator=%1$s" class="delete-link">Delete</a>',$item[$column_name],$currentPageUrl[0],$settings);
				case 'registerTimestamp':
					return $item[$column_name];
				case 'poolingTime':
					return $item[$column_name];
				case 'status':
					return $item[$column_name];
			}
		}
		function prepare() {
			try {
				$db = new Database(HOST,DB,USER,PASS);
				$this->list = $db->listCoordinators();
			} catch(Exception $ex) {
				$errors[] = printError(__FILE__,$ex->getMessage());
				return 0;
			}
		}
	}
	
	$errors = array();
	$messages = array();
	$res = 0;
	if(isset($_REQUEST['action'])) {
		$res = CoordinatorsPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
	
	$forms = array();
	$elements = array();
	// register coordinator
	$elements[] = array(
		'Device ID' 	=> '<input type="text" name="deviceId" id="deviceId" value="'.(($res<=0 && isset($_POST['deviceId']))?$_POST['deviceId']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />',
		'Pooling Time' 	=> '<input type="text" name="poolingTime" id="poolingTime" value="'.(($res<=0 && isset($_POST['poolingTime']))?$_POST['poolingTime']:'').'" '.($res==-2?'style="border-color:#C67171"':'').' /> seconds'
	);
	$formCoordinators = new Form($currentPageUrl[0].'?settings='.$settings,/*pageName*/'Coordinators','register-coordinator',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Register');
	$forms[] = $formCoordinators;
	// list coordinators
	$coordinatorsList = new CoordinatorsList();
	$coordinatorsList->prepare();
	$formListCoordinators = new FormList($currentPageUrl[0].'?settings='.$settings,/*formName*/NULL,'list-coordinators',$coordinatorsList);
	$forms[] = $formListCoordinators;
	// print coordinators page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new CoordinatorsPage(/*pageName*/'Coordinators',$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);
