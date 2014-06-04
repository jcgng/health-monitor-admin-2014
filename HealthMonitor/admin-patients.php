<?php
// if (!current_user_can('admin_patients'))
//  	die('You do not have sufficient permissions to access this page.');
	
	$currentPageUrl = explode('?',Page::currentPageURL());
	
	class PatientsPage extends Page {
		public static function process_submit($type,array &$errors,array &$messages) {
			printInfo(__FILE__,"Process Submit - $type");
			switch($type) {
				case 'register-patient':
					$name = $_POST['name'];
					if(is_empty($name)) {
						$errors[] = printError(__FILE__,'Missing patient name.');
						return -1;
					}
					$deviceId = $_POST['deviceId'];
					if(is_empty($deviceId)) {
						$errors[] = printError(__FILE__,'Missing board.');
						return 0;
					}
					$bedNumber = $_POST['bedNumber'];
					if(is_empty($bedNumber) || !is_numeric($bedNumber)) {
						$errors[] = printError(__FILE__,'Missing patient bed number.');
						return -3;
					}
					$registerTimestamp = $_POST['registerTimestamp'];
					if(is_empty($registerTimestamp)) {
						$errors[] = printError(__FILE__,'Missing patient register date.');
						return 0;
					}
					if(!is_numeric($_POST['birthYear']) || !is_numeric($_POST['birthMonth']) || !is_numeric($_POST['birthDay'])) {
						$errors[] = printError(__FILE__,'Invalid patient birthday.');
						return 0;
					}
					$birthday = $_POST['birthYear'].'-'.$_POST['birthMonth'].'-'.$_POST['birthDay'];
					if(is_empty($birthday)) {
						$errors[] = printError(__FILE__,'Missing patient birthday.');
						return 0;
					}
					$diagnosis = $_POST['diagnosis'];
					if(is_empty($diagnosis)) {
						$errors[] = printError(__FILE__,'Missing patient diagnosis.');
						return -4;
					}
					$background = $_POST['background'];
					if(is_empty($background)) {
						$errors[] = printError(__FILE__,'Missing patient background.');
						return -5;
					}
					$gender = $_POST['gender'];
					$age = date('Y-m-d') - date($birthday);
					// upload photo
					$photo = NULL;
					$allowedExts = array("gif", "jpeg", "jpg", "png");
					$temp = explode(".", $_FILES["photo"]["name"]);
					$extension = end($temp);
					if ((($_FILES["photo"]["type"] == "image/gif")
							|| ($_FILES["photo"]["type"] == "image/jpeg")
							|| ($_FILES["photo"]["type"] == "image/jpg")
							|| ($_FILES["photo"]["type"] == "image/pjpeg")
							|| ($_FILES["photo"]["type"] == "image/x-png")
							|| ($_FILES["photo"]["type"] == "image/png"))
							/* && ($_FILES["photo"]["size"] < 20000)*/ 
							&& in_array($extension, $allowedExts)) {
						if ($_FILES["photo"]["error"] > 0) {
							$errors[] = printError(__FILE__,'Error uploading photos: ' . $_FILES["photo"]["error"]);
						} else {
							if(file_exists("uploaded/" . $_FILES["photo"]["name"])) {
								$errors[] = printError(__FILE__,'File already exists.');
							} else {
								if(move_uploaded_file($_FILES["photo"]["tmp_name"],"uploaded/" . $_FILES["photo"]["name"])) {
									$photo = $_FILES["photo"]["name"];
								}
							}
						}
					} else {
						$errors[] = printError(__FILE__,'Empty or invalid file format.');
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(($idPatients = $db->registerPatient($name,$age,$gender,$bedNumber,$deviceId,$registerTimestamp,$birthday,$diagnosis,$background,$photo))<=0) {
							$errors[] = printError(__FILE__,'Error registering patient.');
							return 0;
						}
						if(isset($_POST['medications']) && isset($_POST['dosages']) && isset($_POST['routes']) && isset($_POST['schedules'])) {
							$medications = $_POST['medications'];
							$dosages = $_POST['dosages'];
							$routes = $_POST['routes'];
							$schedules = $_POST['schedules'];
							if(is_array($medications)) {
								foreach($medications as $medication) {
									try {
										if(!$db->addMedicationToPatient($idPatients, $medication, $dosages[$medication],$routes[$medication],$schedules[$medication])) {
											$errors[] = printError(__FILE__,'Error adding medications to patient.');
										}
									} catch(Exception $ex) {
										$errors[] = printError(__FILE__,$ex->getMessage());
									}
								}
							}
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Patient successfully registered.');
					break;
				case 'delete':
					$idPatients = $_REQUEST['patient'];
					if(is_empty($idPatients)) {
						$errors[] = printError(__FILE__,'Missing patient ID.');
						return 0;
					}
					try {
						$db = new Database(HOST,DB,USER,PASS);
						if(!$db->deletePatient($idPatients)) {
							$errors[] = printError(__FILE__,'Error deleting patient.');
							return 0;
						}
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$messages[] = printInfo(__FILE__, 'Patient successfully deleted.');
					break;
			}
			return 1;
		}
	}
	class PatientsList extends ListTable {
		function getColumns() {
			return array('idPatients' => 'ID', 'photo' => 'Photo', 'registerTimestamp' => 'Registered', 'name' => 'Name', 'birthday' => 'Birthday', 'age' => 'Age', 'gender' => 'Gender', 'bedNumber' => 'Bed', 'diagnosis' => 'Diagnosis', 'background' => 'Background', 'Boards_deviceId' => 'Board', 'Medications_idMedications' => 'Medications');
		}
		function defaultColumns($item,$column_name) {
			switch($column_name) {
				case 'idPatients':
					$currentPageUrl = explode('?',Page::currentPageURL());
					$settings = (isset($_REQUEST['settings'])?$_REQUEST['settings']:'');
					return sprintf('%1$s</br><a href="%2$s?settings=%3$s&action=delete&patient=%1$s" class="delete-link">Delete</a>',$item[$column_name],$currentPageUrl[0],$settings);
				case 'name':
					return $item[$column_name];
				case 'birthday':
					return $item[$column_name];
				case 'age':
					return $item[$column_name];
				case 'gender':
					return $item[$column_name];
				case 'bedNumber':
					return $item[$column_name];
				case 'registerTimestamp':
					return $item[$column_name];
				case 'diagnosis':
					return $item[$column_name];
				case 'background':
					return $item[$column_name];
				case 'photo':
					if(!is_empty($item[$column_name]))
						return '<img src="uploaded/'.basename($item[$column_name]).'" height="50"/>';
					else
						return $item[$column_name];
				case 'Boards_deviceId':
					return $item[$column_name];
				case 'Medications_idMedications':
					try {
						$db = new Database(HOST,DB,USER,PASS);
						$medications = $db->listPatientMedications($item['idPatients']);
					} catch(Exception $ex) {
						$errors[] = printError(__FILE__,$ex->getMessage());
						return 0;
					}
					$sep = '';
					$str = '';
					foreach($medications as $medication) {
						$str .= $sep.$medication['drug'] . ' : ' . $medication['patientDosage'] . ' / ' . $medication['schedule'] . ' / ' . $medication['route']; 
						$sep = '</br>';
					}
					return $str;
			}
		}
		function prepare() {
			try {
				$db = new Database(HOST,DB,USER,PASS);
				$this->list = $db->listPatients();
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
		$res = PatientsPage::process_submit($_REQUEST['action'],$errors,$messages);
	}
	
	$forms = array();
	try {
		$db = new Database(HOST,DB,USER,PASS);
		$boards = $db->listUnassociatedPatientsBoards();
		$medications = $db->listMedications();
	} catch(Exception $ex) {
		$errors[] = printError(__FILE__,$ex->getMessage());
	}
	$optionsBoards = '';
	foreach($boards as $board) {
		$optionsBoards .=  '<option value="'.$board['deviceId'].'">'.$board['deviceId'].'</option>';
	}
	$optionsMedications = '';
	foreach($medications as $medication) {
		$optionsMedications .=  
			'<input type="checkbox" name="medications[]" value="'.$medication['idMedications'].'">'.$medication['drug'].'</input>
			<select name="routes['.$medication['idMedications'].']" id="routes['.$medication['idMedications'].']"><option value="oral">Oral</option><option value="rectally">Rectally</option><option value="intravenous">Intravenous</option><option value="subcutaneous">Subcutaneous</option><option value="cutaneous">Cutaneous</option><option value="nasal">Nasal</option><option value="intravitreal">Intravitreal</option></select>
			<input type="text" name="dosages['.$medication['idMedications'].']" id="dosages['.$medication['idMedications'].']" value="insert dosage here..."/>
			<select name="schedules['.$medication['idMedications'].']" id="schedules['.$medication['idMedications'].']"><option value="4h/4h">4h/4h</option><option value="6h/6h">6h/6h</option><option value="8h/8h">8h/8h</option><option value="12h/12h">12h/12h</option><option value="24h/24h">24h/24h</option></select></br>';
	}
	$selectBirthday = '<select name="birthYear"><option>-- Year --</option>';
	for($year=(date('Y')-110);$year<=date('Y');$year++) $selectBirthday .= "<option value='$year'>$year</option>";	
	$selectBirthday .= '</select>';
	
	$selectBirthday .= '<select name="birthMonth"><option>-- Month --</option>';
	for($month=1;$month<=12;$month++) $selectBirthday .= "<option value=".($month<10?"0$month":$month).">".($month<10?"0$month":$month)."</option>";
	$selectBirthday .= '</select>';
	
	$selectBirthday .= '<select name="birthDay"><option>-- Day --</option>';
	for($day=1;$day<=31;$day++) $selectBirthday .= "<option value=".($day<10?"0$day":$day).">".($day<10?"0$day":$day)."</option>";
	$selectBirthday .= '</select>';

	// register patient
	$elements = array(
			array('Name' => '<input type="text" name="name" id="name" value="'.(($res<=0 && isset($_POST['name']))?$_POST['name']:'').'" '.($res==-1?'style="border-color:#C67171"':'').' />'),
			array('Bed' => '<input type="text" name="bedNumber" id="bedNumber" value="'.(($res<=0 && isset($_POST['bedNumber']))?$_POST['bedNumber']:'').'" '.($res==-3?'style="border-color:#C67171"':'').' />'),
			array('Register Date' => GenericForm::dateSelect('registerTimestamp',date('Y-m-d',(time() - (60*24*3600))),date('Y-m-d'),date('Y-m-d'))),
			array('Birthday' => $selectBirthday),
			array('Gender' => '<select name="gender" id="gender"><option value="M">M</option><option value="F">F</option></select>'),
			array('Diagnosis' => '<textarea name="diagnosis" id="diagnosis" rows="4" cols="50" '.($res==-4?'style="border-color:#C67171"':'').'>'.(($res<=0 && isset($_POST['diagnosis']))?$_POST['diagnosis']:'').'</textarea>'),
			array('Background' => '<textarea name="background" id="background" rows="4" cols="50" '.($res==-5?'style="border-color:#C67171"':'').'>'.(($res<=0 && isset($_POST['background']))?$_POST['background']:'').'</textarea>'),
			array('Board' => '<select name="deviceId" id="deviceId">'.$optionsBoards.'</select>'),
			array('Photo' => '<input type="file" name="photo" id="photo"/>'),
			array('Medications' => $optionsMedications));
	$formPatients = new Form($currentPageUrl[0].'?settings='.$settings,$pageName,'register-patient',$elements,/*submitButton*/true,/*onSubmit*/NULL,/*submitButtonName*/'Register');
	$forms[] = $formPatients;
	// list patients
	$patientsList = new PatientsList();
	$patientsList->prepare();
	$formListPatients = new FormList($currentPageUrl[0].'?settings='.$settings,/*formName*/NULL,'list-patients',$patientsList);
	$forms[] = $formListPatients;
	// print patients page
	$css = array();
	$javascript = array();
	$javascript_functions = array();
	$adminPage = new PatientsPage($pageName,$pageIcon=NULL,$css,$javascript,$javascript_functions,$forms);
	$adminPage->printPage($errors,$messages);