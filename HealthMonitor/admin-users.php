<?php
// 	if (!current_user_can('admin_users'))
// 		die('You do not have sufficient permissions to access this page.');

	$currentPageUrl = explode('?',Page::currentPageURL());

	class UsersPage extends Page {
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'register-user':
					$userName = $_POST['userName'];
					if(is_empty($userName)) {
						$errors[] = printError(__FILE__,'Missing user name.');
						return -1;
					}
					$password = $_POST['password'];
					if(is_empty($password)) {
						$errors[] = printError(__FILE__,'Missing user password.');
						return -2;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(($idUsers = $db->registerUser($userName,encodeXT($password)))<=0) {
							$errors[] = printError(__FILE__,'Error registering user.');
							return 0;
						}
						if(isset($_POST['deviceId'])) {
							$deviceId = $_POST['deviceId'];
							if(is_empty($deviceId)) {
								$errors[] = printError(__FILE__,'Missing board.');
								return 0;
							}
							if(is_array($deviceId)) {
								foreach($deviceId as $id) {
									try {
										if(!$db->addBoardToUser($idUsers,$id)) {
											$errors[] = printError(__FILE__,'Error adding board to user.');
										}
									} catch(Exception $ex) {
										$errors[] = printError(__FILE__,$ex->getMessage());
									}
								}
							}
						} else {
							$errors[] = printError(__FILE__,'Missing board.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'User successfully registered.');
					break;
				case 'delete':
					$idUsers = $_REQUEST['user'];
					if(is_empty($idUsers)) {
						$errors[] = printError(__FILE__,'Missing user ID.');
						return 0;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->deleteUser($idUsers)) {
							$errors[] = printError(__FILE__,'Error deleting user.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'User successfully deleted.');
					break;
			}
			return 1;
		}
	}
	class UsersList extends ListTable {
		function getColumns() {
			return array('idUsers' => 'ID', 'userName' => 'userName', 'registerTimestamp' => 'Timestamp', 'status' => 'Status', 'Boards_deviceId' => 'Boards');
		}
		function defaultColumns($item,$column_name) {
			switch($column_name) {
				case 'idUsers':
					$currentPageUrl = explode('?',Page::currentPageURL());
					$settings = (isset($_REQUEST['settings'])?$_REQUEST['settings']:'');
					return sprintf('%1$s</br><a href="%2$s?settings=%3$s&action=delete&user=%1$s" class="delete-link">Delete</a>',$item[$column_name],$currentPageUrl[0],$settings);
				case 'userName':
					return $item[$column_name];
				case 'registerTimestamp':
					return $item[$column_name];
				case 'status':
					return $item[$column_name];
				case 'Boards_deviceId':
					try {
						$db = new Database(HOST,DB,USER,PASS);
						$boards = $db->listUserBoards($item['idUsers']);
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$sep = '';
					$str = '';
					foreach($boards as $board) {
						$str .= $sep.$board['deviceId'];
						$sep = ',';
					}
					return $str;
			}
		}
		function prepare() {
			try {
				$db = new Database(HOST,DB,USER,PASS);
				$this->list = $db->listUsers();
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
		$res = UsersPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
	
	$forms = array();
	try {
		$db = new Database(HOST,DB,USER,PASS);
		$boards = $db->listUnassociatedUsersBoards();
	} catch(Exception $ex) {
		$errors[] = printError(__FILE__,$ex->getMessage());
	}
	$options = '';
	foreach($boards as $board) {
// 		$options .=  '<option value="'.$board['deviceId'].'">'.$board['deviceId'].'</option>';
		$options .= '<input type="checkbox" name="deviceId[]" value="'.$board['deviceId'].'">'.$board['deviceId'].'</input></br>';
	}
	// register user
	$elements = array(
			array('User Name' => '<input type="text" name="userName" id="userName" value="'.(($res<=0 && isset($_POST['userName']))?$_POST['userName']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />'),
			array('Password' => '<input type="password" name="password" id="password" value="'.(($res<=0 && isset($_POST['password']))?$_POST['password']:'').'" '.($res==-2?'style="border-color:#C67171"':'').' />'),
			array('Boards' => (is_empty($options)?'No boards available':$options)));
	$formUsers = new Form($currentPageUrl[0].'?settings='.$settings,$pageName,'register-user',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Register');
	$forms[] = $formUsers;
	// list users
	$usersList = new UsersList();
	$usersList->prepare();
	$formListUsers = new FormList($currentPageUrl[0].'?settings='.$settings,/*formName*/NULL,'list-users',$usersList);
	$forms[] = $formListUsers;
	// print users page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new UsersPage($pageName,$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);
