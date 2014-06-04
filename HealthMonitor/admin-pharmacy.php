<?php
// 	if (!current_user_can('admin_pharmacy'))
// 		die('You do not have sufficient permissions to access this page.');

	$currentPageUrl = explode('?',Page::currentPageURL());

	class PharmacyPage extends Page {
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'register-medication':
					$drug = $_POST['drug'];
					if(is_empty($drug)) {
						$errors[] = printError(__FILE__,'Missing medication drug.');
						return -1;
					}
					$dosage = $_POST['dosage'];
					if(is_empty($drug) || !is_numeric($dosage)) {
						$errors[] = printError(__FILE__,'Missing or invalid medication dosage.');
						return -1;
					}
					$units = $_POST['units'];
					if(is_empty($units)) {
						$errors[] = printError(__FILE__,'Missing or invalid medication units.');
						return -1;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->registerMedication($drug,$dosage,$units)) {
							$errors[] = printError(__FILE__,'Error registering medication.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Medication successfully registered.');
					break;
				case 'delete':
					$idMedications = $_REQUEST['medication'];
					if(is_empty($idMedications)) {
						$errors[] = printError(__FILE__,'Missing medication ID.');
						return 0;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->deleteMedication($idMedications)) {
							$errors[] = printError(__FILE__,'Error deleting medication.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Medication successfully deleted.');
					break;
			}
			return 1;
		}
	}
	class MedicationsList extends ListTable {
		function getColumns() {
			return array('idMedications'=> 'ID', 'drug' => 'Drug', 'dosage' => 'Dosage', 'units' => 'Units');
		}
		function defaultColumns($item,$column_name) {
			switch($column_name) {
				case 'idMedications':
					$currentPageUrl = explode('?',Page::currentPageURL());
					$settings = (isset($_REQUEST['settings'])?$_REQUEST['settings']:'');
					return sprintf('%1$s</br><a href="%2$s?settings=%3$s&action=delete&medication=%1$s" class="delete-link">Delete</a>',$item[$column_name],$currentPageUrl[0],$settings);
				case 'drug':
					return $item[$column_name];
				case 'dosage':
					return $item[$column_name];
				case 'units':
					return $item[$column_name];
			}
		}
		function prepare() {
			try {
				$db = new Database(HOST,DB,USER,PASS);
				$this->list = $db->listMedications();
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
		$res = PharmacyPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
	
	$forms = array();
	// register medication
	$elements[] = array(
			'Drug' => '<input type="text" name="drug" id="drug" value="'.(($res<=0 && isset($_POST['drug']))?$_POST['drug']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />',
			'Dosage' => '<input type="text" name="dosage" id="dosage" value="'.(($res<=0 && isset($_POST['dosage']))?$_POST['dosage']:'').'" '.($res==-2?'style="border-color:#C67171"':'').' />',
			'Units' => '<select name="units" id="units"><option value="mg">mg</option><option value="ml">ml</option><option value="mg/ml">mg/ml</option></select>'
	);
	$formPharmacy = new Form($currentPageUrl[0].'?settings='.$settings,$pageName,'register-medication',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Register');
	$forms[] = $formPharmacy;
	// list medications
	$medicationsList = new MedicationsList();
	$medicationsList->prepare();
	$formListMedications = new FormList($currentPageUrl[0].'?settings='.$settings,/*formName*/NULL,'list-medications',$medicationsList);
	$forms[] = $formListMedications;
	// print medications page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new PharmacyPage($pageName,$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);
