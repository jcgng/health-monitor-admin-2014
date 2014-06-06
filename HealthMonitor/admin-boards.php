<?php
	$currentPageUrl = explode('?',Page::currentPageURL());

	class BoardsPage extends Page {
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'register-board':
					$deviceId = $_POST['deviceId'];
					if(is_empty($deviceId)) {
						$errors[] = printError(__FILE__,'Missing board device ID.');
						return -1;
					}
					$coordinatorDeviceId = $_POST['coordinatorDeviceId'];
					if(is_empty($coordinatorDeviceId)) {
						$errors[] = printError(__FILE__,'Missing coordinator device ID.');
						return 0;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->registerBoard($deviceId,$coordinatorDeviceId)) {
							$errors[] = printError(__FILE__,'Error registering board.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Board successfully registered.');
					break;
				case 'delete-board':
					$deviceId = $_REQUEST['board'];
					if(is_empty($deviceId)) {
						$errors[] = printError(__FILE__,'Missing device ID.');
						return 0;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->deleteBoard($deviceId)) {
							$errors[] = printError(__FILE__,'Error deleting board.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Board successfully deleted.');
					break;
			}
			return 1;
		}
	}
	class BoardsList extends ListTable {
		function getColumns() {
			return array('deviceId' => 'Device', 'status' => 'Status', 'registerTimestamp' => 'Timestamp', 'Coordinators_deviceId' => 'Coordinator');
		}
		function defaultColumns($item,$column_name) {
			switch($column_name) {
				case 'deviceId':
					$currentPageUrl = explode('?',Page::currentPageURL());
					$settings = (isset($_REQUEST['settings'])?$_REQUEST['settings']:'');
					return sprintf('%1$s</br><a href="%2$s?settings=%3$s&action=delete-board&board=%1$s" class="delete-link">Delete</a>',$item[$column_name],$currentPageUrl[0],$settings);
				case 'status':
					return $item[$column_name];
				case 'registerTimestamp':
					return $item[$column_name];
				case 'Coordinators_deviceId':
					return $item[$column_name];
			}
		}
		function prepare() {
			try {
				$db = new Database(HOST,DB,USER,PASS);
				$this->list = $db->listBoards();
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
		$res = BoardsPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
	try {
		$db = new Database(HOST,DB,USER,PASS);
		$coordinators = $db->listCoordinators();
	} catch(Exception $ex) {
		$errors[] = printError(__FILE__,$ex->getMessage());
	}
	$options = '';
	foreach($coordinators as $coordinator) {
		$options .=  '<option value="'.$coordinator['deviceId'].'">'.$coordinator['deviceId'].'</option>';
	}
	$forms = array();
	$elements = array();
	// register board
	$elements[] = array(
		'Device ID' 	=> '<input type="text" name="deviceId" id="deviceId" value="'.(($res<=0 && isset($_POST['deviceId']))?$_POST['deviceId']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />',
		'Coordinator' 	=> '<select id="coordinatorDeviceId" name="coordinatorDeviceId">'.$options.'</select>');
	$formBoards = new Form($currentPageUrl[0].'?settings='.$settings,/*pageName*/'Boards','register-board',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Register');
	$forms[] = $formBoards;
	// list boards
	$boardsList = new BoardsList();
	$boardsList->prepare();
	$formListBoards = new FormList($currentPageUrl[0].'?settings='.$settings,/*formName*/NULL,'list-boards',$boardsList);
	$forms[] = $formListBoards;
	// print boards page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new BoardsPage(/*pageName*/'Boards',$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);
