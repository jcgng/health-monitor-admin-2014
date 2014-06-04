<?php
//	if (!current_user_can('admin_tests'))
//		die('You do not have sufficient permissions to access this page.');
	class TestsPage extends Page {
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'save-health':
					$bpm = $_POST['bpm'];
					if(is_empty($bpm) || !is_numeric($bpm)) {
						$errors[] = printError(__FILE__,'Missing BPM value.');
						return -1;
					}
					$temperature = $_POST['temperature'];
					if(is_empty($temperature) || !is_numeric($temperature)) {
						$errors[] = printError(__FILE__,'Missing temperature value.');
						return -2;
					}
					$balance = $_POST['balance'];
					if(is_empty($balance) || !is_numeric($balance)) {
						$errors[] = printError(__FILE__,'Missing balance value.');
						return -3;
					}
					$deviceId = $_POST['deviceId'];
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if($db->saveHealth($bpm, $temperature, $balance, $deviceId)<=0) {
							$errors[] = printError(__FILE__,'Error saving health values.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Health values successfully saved.');
					break;
			}
			return 1;
		}
	}
	class TestsList extends ListTable {
		function getColumns() {
			return array('Id' => 'ID', 'param1' => 'Parameter', 'val1' => 'Value', 'dateTime' => 'Timestamp', 'deviceId' => 'Board');
		}
		function defaultColumns($item,$column_name) {
			switch($column_name) {
				case 'Id':
					return $item[$column_name];
				case 'dateTime':
					return $item[$column_name];
				case 'deviceId':
					return $item[$column_name];
				case 'param1':
					return $item[$column_name];
				case 'val1':
					return $item[$column_name];
					
			}
			
		}
		function prepare() {
			try {
				$db = new Database(HOST,DB,USER,PASS);
				$this->list = $db->listHealth(/*deviceId*/NULL,/*idPatients*/NULL,/*bedNumber*/NULL,/*orderby*/"Id",/*order*/"DESC");
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
		$res = TestsPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
	
	$forms = array();
	try {
		$db = new Database(HOST,DB,USER,PASS);
		$boards = $db->listBoards();
	} catch(Exception $ex) {
		$errors[] = printError(__FILE__,$ex->getMessage());
	}
	$options = '';
	foreach($boards as $board) {
		$options .=  '<option value="'.$board['deviceId'].'">'.$board['deviceId'].'</option>';
	}
	// save test
	$elements = array(
			array('BPM' => '<input type="text" name="bpm" id="bpm" value="'.(($res<=0 && isset($_POST['bpm']))?$_POST['bpm']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />'),
			array('Temperature' => '<input type="text" name="temperature" id="temperature" value="'.(($res<=0 && isset($_POST['temperature']))?$_POST['temperature']:'').'" '.($res==-2?'style="border-color:#C67171"':'').' />'),
			array('Balance' => '<input type="text" name="balance" id="balance" value="'.(($res<=0 && isset($_POST['balance']))?$_POST['balance']:'').'" '.($res==-3?'style="border-color:#C67171"':'').' />'),
			array('Board' => '<select name="deviceId" id="deviceId">'.$options.'</select>'));
	$formTests = new Form($currentPageUrl[0].'?settings='.$settings,$pageName,'save-health',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Save');
	$forms[] = $formTests;
	// list tests
	$testsList = new TestsList();
	$testsList->prepare();
	$formListTests = new FormList($currentPageUrl[0].'?settings='.$settings,/*formName*/NULL,'list-tests',$testsList);
	$forms[] = $formListTests;
	// print tests page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new TestsPage($pageName,$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);