<?php
/** 
 *	}
 */
require_once("resources/functions.php");

class Database {
	private $link;
	function __construct($host,$db,$user,$pass) {
		try {
			$this->link = mysqli_connect($host,$user,$pass,$db);
			// check connection
			if (mysqli_connect_errno()) {
    			throw new Exception("Connect failed: %s!", mysqli_connect_error());
				exit();
			}
			if(!$this->link) {
				throw new Exception("Error connecting to database!");
			}
			mysqli_set_charset($this->link,"utf8");
		} catch(Exception $ex) {
    		throw new Exception($ex->getMessage());
    	}
	}
	
	function __destruct() {
		mysqli_close($this->link);
	}

	private function simpleSelect($from,$where=1,$orderby=NULL,$order=NULL,$limit=30) {
		$query = "SELECT * FROM `$from` WHERE $where ".($orderby!=NULL?"ORDER BY $orderby":"")." $order LIMIT $limit";
		try {
			$res = mysqli_query($this->link,$query);
			while ($row = mysqli_fetch_assoc($res)) {
				$return[] = $row;
			}	
			return $return;
		} catch(Exception $ex) {
    		throw new Exception($ex->getMessage());
    	}
	}
	
	private function simpleInsert($into,$columns,$values) {
		$query = "INSERT INTO `$into` ($columns) VALUES ($values)";
		try {
			return mysqli_query($this->link,$query);
		} catch(Exception $ex) {
    		throw new Exception($ex->getMessage());
    	}
	}
	
	private function simpleDelete($from,$where) {
		$query = "DELETE FROM `$from` WHERE $where";
		try {
			return mysqli_query($this->link,$query);
		} catch(Exception $ex) {
    		throw new Exception($ex->getMessage());
    	}
	}
	
	function listHealth($deviceId=NULL,$idPatients=NULL,$bedNumber=NULL,$orderby=NULL,$order=NULL,$limit=10) {
		$where = NULL;
		if($deviceId!=NULL) {
			$where = "`deviceId` = ?";
			if($idPatients!=NULL)
				$where .= " AND `idPatients` = ?";
			if($bedNumber!=NULL)
				$where .= " AND `bedNumber` = ?";
		} else if($idPatients!=NULL) {
			$where = "`idPatients` = ?";
			if($bedNumber!=NULL)
				$where .= " AND `bedNumber` = ?";
		} else if($bedNumber!=NULL) {
			$where .= "`bedNumber` = ?";
		}
		$query = "SELECT * FROM `viewHealth` ".($where!=NULL?"WHERE $where":'')." ".($orderby!=NULL?"ORDER BY $orderby":"")." $order LIMIT $limit";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				if($deviceId!=NULL) {
					if($idPatients!=NULL) {
						if($bedNumber!=NULL)
							mysqli_stmt_bind_param($stmt, "sdd", $deviceId, $idPatients, $bedNumber);
						else
							mysqli_stmt_bind_param($stmt, "sd", $deviceId, $idPatients);
					} else {
						if($bedNumber!=NULL)
							mysqli_stmt_bind_param($stmt, "sd", $deviceId, $bedNumber);
						else
							mysqli_stmt_bind_param($stmt, "s", $deviceId);
					}
				} else if($idPatients!=NULL) {
					if($bedNumber!=NULL)
						mysqli_stmt_bind_param($stmt, "dd", $idPatients, $bedNumber);
					else
						mysqli_stmt_bind_param($stmt, "d", $idPatients);
				} else if($bedNumber!=NULL) {
					mysqli_stmt_bind_param($stmt, "d", $bedNumber);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$id,$dateTime,$param1,$val1,$deviceId,$idPatients,$name,$age,$gender,$bedNumber,$userName,$password);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('Id'=>$id,'dateTime'=>$dateTime,'param1'=>$param1,'val1'=>$val1,'deviceId'=>$deviceId,'idPatients'=>$idPatients,'name'=>$name,'age'=>$age,'gender'=>$gender,'bedNumber'=>$bedNumber,'userName'=>$userName,'password'=>$password);
				}

				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
	    	throw new Exception($ex->getMessage());
	    }
	}
	
	function listLastHealth($deviceId=NULL,$idPatients=NULL,$bedNumber=NULL,$orderby=NULL,$order=NULL,$limit=10) {
		$where = NULL;
		if($deviceId!=NULL) {
			$where = "`deviceId` = ?";
			if($idPatients!=NULL)
				$where .= " AND `idPatients` = ?";
			if($bedNumber!=NULL)
				$where .= " AND `bedNumber` = ?";
		} else if($idPatients!=NULL) {
			$where = "`idPatients` = ?";
			if($bedNumber!=NULL)
				$where .= " AND `bedNumber` = ?";
		} else if($bedNumber!=NULL) {
			$where .= "`bedNumber` = ?";
		}
		$query = "SELECT * FROM (SELECT * FROM `viewHealth` ".($orderby!=NULL?"ORDER BY $orderby":"")." $order) AS `viewHealth` ".($where!=NULL?"WHERE $where":'')." GROUP BY `param1` LIMIT $limit";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				if($deviceId!=NULL) {
					if($idPatients!=NULL) {
						if($bedNumber!=NULL)
							mysqli_stmt_bind_param($stmt, "sdd", $deviceId, $idPatients, $bedNumber);
						else
							mysqli_stmt_bind_param($stmt, "sd", $deviceId, $idPatients);
					} else {
						if($bedNumber!=NULL)
							mysqli_stmt_bind_param($stmt, "sd", $deviceId, $bedNumber);
						else
							mysqli_stmt_bind_param($stmt, "s", $deviceId);
					}
				} else if($idPatients!=NULL) {
					if($bedNumber!=NULL)
						mysqli_stmt_bind_param($stmt, "dd", $idPatients, $bedNumber);
					else
						mysqli_stmt_bind_param($stmt, "d", $idPatients);
				} else if($bedNumber!=NULL) {
					mysqli_stmt_bind_param($stmt, "d", $bedNumber);
				}
				mysqli_stmt_execute($stmt);
				// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$id,$dateTime,$param1,$val1,$deviceId,$idPatients,$name,$age,$gender,$bedNumber,$userName,$password);
				$return = array();
				// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
					// 					$return[] = $row;
					$return[] = array('Id'=>$id,'dateTime'=>$dateTime,'param1'=>$param1,'val1'=>$val1,'deviceId'=>$deviceId,'idPatients'=>$idPatients,'name'=>$name,'age'=>$age,'gender'=>$gender,'bedNumber'=>$bedNumber,'userName'=>$userName,'password'=>$password);
				}
	
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function saveHealth($bpm,$temperature,$balance,$deviceId) {
		$query = "INSERT INTO `tblHealth` (`param1`,`val1`,`Boards_deviceId`) VALUES ('bpm', ?, ?),('temp', ?, ?),('move', ?, ?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt,"dsdsds",$bpm,$deviceId,$temperature,$deviceId,$balance,$deviceId);
				mysqli_stmt_execute($stmt);
				$res = mysqli_insert_id($this->link);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listBoards($deviceId=NULL) {
		$where = NULL;
		if($deviceId!=NULL) {
			$where = "deviceId = ?";
		}
		$query = "SELECT * FROM `tblBoards` ".($where!=NULL?"WHERE $where":'');
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				if($deviceId!=NULL) {
					mysqli_stmt_bind_param($stmt, "s", $deviceId);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$deviceId,$registerTimestamp);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('deviceId'=>$deviceId,'registerTimestamp'=>$registerTimestamp);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listUserBoards($idUsers) {
		$query = "SELECT * FROM `viewBoardsByUsers` WHERE idUsers = ?";
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "d", $idUsers);
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$deviceId,$idUsers,$userName,$password,$localServer,$status,$registerTimestamp);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('deviceId'=>$deviceId);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listUnassociatedUsersBoards() {
		$query = "SELECT * FROM `viewBoardsByUsers` WHERE idUsers IS NULL";
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$deviceId,$idUsers,$userName,$password,$localServer,$status,$registerTimestamp);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('deviceId'=>$deviceId);
				}	
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listUnassociatedPatientsBoards() {
		$query = "SELECT * FROM `viewBoardsByPatients` WHERE idPatients IS NULL";
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_execute($stmt);
				// $res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$deviceId,$idPatients,$name,$age,$gender,$bedNumber);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('deviceId'=>$deviceId,'idPatients'=>$idPatients,'name'=>$name,'age'=>$age,'gender'=>$gender,'bedNumber'=>$bedNumber);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function registerBoard($deviceId) {
		$query = "INSERT INTO `tblBoards` (`deviceId`) VALUES (?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "s", $deviceId);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}

	function deleteBoard($deviceId) {
		$query = "DELETE FROM `tblBoards` WHERE deviceId=?";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "d", $deviceId);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listUsers($idUsers=NULL) {
		$where = NULL;
		if($idUsers!=NULL) {
			$where = "idUsers = ?";
		}
		$query = "SELECT * FROM `tblUsers` ".($where!=NULL?"WHERE $where":'');
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				if($idUsers!=NULL) {
					mysqli_stmt_bind_param($stmt, "d", $idUsers);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$idUsers,$userName,$password,$localServer,$status,$registerTimestamp);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('idUsers'=>$idUsers,'userName'=>$userName,'password'=>$password,'localServer'=>$localServer,'status'=>$status,'registerTimestamp'=>$registerTimestamp);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function registerUser($userName,$password) {
		$query = "INSERT INTO `tblUsers` (`userName`,`password`) VALUES (?, ?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt,"ss",$userName,$password);
				mysqli_stmt_execute($stmt);
				$res = mysqli_insert_id($this->link);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function addBoardToUser($idUsers,$deviceId) {
		$query = "INSERT INTO `tblUsers_has_tblBoards` (`Users_idUsers`,`Boards_deviceId`) VALUES (?, ?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt,"ds",$idUsers,$deviceId);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function getUser($userName,$password) {
		$where = NULL;
		if($userName!=NULL) {
			$where = "userName = ?";
			if($password!=NULL)
				$where .= " AND password = ?";
		} else if($password!=NULL) {
			$where = "password = ?";
		}
		$query = "SELECT * FROM `tblUsers` ".($where!=NULL?"WHERE $where":'');
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				if($userName!=NULL) {
					if($password!=NULL)
						mysqli_stmt_bind_param($stmt, "ss", $userName, $password);
					else
						mysqli_stmt_bind_param($stmt, "s", $userName);
				} else if($password!=NULL) {
					mysqli_stmt_bind_param($stmt, "s", $password);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$idUsers,$userName,$password,$localServer,$status,$registerTimestamp);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('idUsers'=>$idUsers,'userName'=>$userName,'password'=>$password,'localServer'=>$localServer,'status'=>$status,'registerTimestamp'=>$registerTimestamp);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function isUserBoard($userName,$deviceId) {
		$where = NULL;
		if($userName!=NULL) {
			$where = "userName = ?";
			if($deviceId!=NULL)
				$where .= " AND deviceId = ?";
		} else if($deviceId!=NULL) {
				$where .= "deviceId = ?";
		}
		$query = "SELECT * FROM `viewBoardsByUsers` ".($where!=NULL?"WHERE $where":'');
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				if($userName!=NULL) {
					if($deviceId!=NULL)
						mysqli_stmt_bind_param($stmt, "ss", $userName, $deviceId);
					else
						mysqli_stmt_bind_param($stmt, "s", $userName);
				} else if($deviceId!=NULL) {
					mysqli_stmt_bind_param($stmt, "s", $deviceId);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_store_result($stmt);
				return (mysqli_stmt_num_rows($stmt)>0?true:false);
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function deleteUser($idUsers) {
		$query = "DELETE FROM `tblUsers` WHERE idUsers=?";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "d", $idUsers);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listPatients($idPatients=NULL,$deviceId=NULL) {
		$where = NULL;
		if($idPatients!=NULL) {
			$where = "idPatients = ?";
			if($deviceId!=NULL)
				$where .= " AND Boards_deviceId = ?";
		} else if($deviceId!=NULL) {
			$where .= "Boards_deviceId = ?";
		}
		$query = "SELECT * FROM `tblPatients` ".($where!=NULL?"WHERE $where":'');
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				if($idPatients!=NULL) {
					if($deviceId!=NULL)
						mysqli_stmt_bind_param($stmt, "ds", $idPatientes, $deviceId);
					else
						mysqli_stmt_bind_param($stmt, "d", $idPatients);
				} else if($deviceId!=NULL) {
					mysqli_stmt_bind_param($stmt, "s", $deviceId);
				}
				mysqli_stmt_execute($stmt);
				mysqli_stmt_bind_result($stmt,$idPatients,$registerTimestamp,$name,$birthday,$age,$gender,$diagnosis,$background,$bedNumber,$photo,$Boards_deviceId);
				$return = array();
				while(mysqli_stmt_fetch($stmt)) {
					$return[] = array('idPatients'=>$idPatients,'registerTimestamp'=>$registerTimestamp,'name'=>$name,'birthday'=>$birthday,'age'=>$age,'gender'=>$gender,'diagnosis'=>$diagnosis,'background'=>$background,'bedNumber'=>$bedNumber,'photo'=>$photo,'Boards_deviceId'=>$Boards_deviceId);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function registerPatient($name,$age,$gender,$bedNumber,$deviceId,$registerTimestamp,$birthday,$diagnosis,$background,$photo) {
		$query = "INSERT INTO `tblPatients` (`name`,`age`,`gender`,`bedNumber`,`Boards_deviceId`,`registerTimestamp`,`birthday`,`diagnosis`,`background`,`photo`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt,"sdsdssssss",$name,$age,$gender,$bedNumber,$deviceId,$registerTimestamp,$birthday,$diagnosis,$background,$photo);
				mysqli_stmt_execute($stmt);
				$res = mysqli_insert_id($this->link);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function deletePatient($idPatients) {
		$query = "DELETE FROM `tblPatients` WHERE idPatients=?";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "d", $idPatients);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function isAdmin($userName,$password) {
		$where = NULL;
		if($userName!=NULL) {
			$where = "userName = ?";
			if($password!=NULL)
				$where .= " AND password = ?";
		} else if($password!=NULL) {
			$where .= "password = ?";
		}
		$query = "SELECT * FROM `tblAdmins` ".($where!=NULL?"WHERE $where":'');
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				if($userName!=NULL) {
					if($password!=NULL)
						mysqli_stmt_bind_param($stmt, "ss", $userName, $password);
					else
						mysqli_stmt_bind_param($stmt, "s", $userName);
				} else if($password!=NULL) {
					mysqli_stmt_bind_param($stmt, "s", $password);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_store_result($stmt);
				return (mysqli_stmt_num_rows($stmt)>0?true:false);
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listMedications($idMedications=NULL) {
		$where = NULL;
		if($idMedications!=NULL) {
			$where = "idMedications = ?";
		}
		$query = "SELECT * FROM `tblMedications` ".($where!=NULL?"WHERE $where":'');
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				if($idMedications!=NULL) {
					mysqli_stmt_bind_param($stmt,"d",$idMedications);
				}
				mysqli_stmt_execute($stmt);
// 				$res = mysqli_stmt_get_result($stmt);
				mysqli_stmt_bind_result($stmt,$idMedications,$drug,$dosage,$units);
				$return = array();
// 				while ($row = mysqli_fetch_assoc($res)) {
				while(mysqli_stmt_fetch($stmt)) {
// 					$return[] = $row;
					$return[] = array('idMedications'=>$idMedications,'drug'=>$drug,'dosage'=>$dosage,'units'=>$units);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function registerMedication($drug,$dosage,$units) {
		$query = "INSERT INTO `tblMedications` (`drug`,`dosage`,`units`) VALUES (?, ?, ?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "sss", $drug,$dosage,$units);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function deleteMedication($idMedications) {
		$query = "DELETE FROM `tblMedications` WHERE idMedications=?";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "d", $idMedications);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function addMedicationToPatient($idPatients,$idMedications,$dosage,$route,$schedule) {
		$query = "INSERT INTO `tblPatients_has_tblMedications` (`Patients_idPatients`,`Medications_idMedications`,`dosage`,`route`,`schedule`) VALUES (?, ?, ?, ?, ?)";
		try {
			if ($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt,"dddss",$idPatients,$idMedications,$dosage,$route,$schedule);
				$res = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				return $res;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	function listPatientMedications($idPatients) {
		$query = "SELECT * FROM `viewMedicationsByPatients` WHERE idPatients = ?";
		try {
			if($stmt = mysqli_prepare($this->link,$query)) {
				mysqli_stmt_bind_param($stmt, "d", $idPatients);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_bind_result($stmt,$idMedications,$drug,$dosage,$units,$idPatients,$patientDosage,$schedule,$route);
				$return = array();
				while(mysqli_stmt_fetch($stmt)) {
					$return[] = array('idMedications'=>$idMedications,'drug'=>$drug,'dosage'=>$dosage,'units'=>$units,'idPatients'=>$idPatients,'patientDosage'=>$patientDosage,'schedule'=>$schedule,'route'=>$route);
				}
				mysqli_stmt_close($stmt);
				return $return;
			}
		} catch(Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
}
