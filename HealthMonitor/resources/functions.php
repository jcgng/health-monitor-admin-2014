<?php
/**
 * Check if variable is empty
 * 
 * @param mixed $var
 * @return boolean
 */
function is_empty($var) {
	if((empty($var) || $var===NULL) && ($var!==FALSE && $var!==0 && $var!=='0')) {
		return true;
	} else {
		return false;
	}
}

/**
 * Validate e-mail format
 * 
 * @param string $email
 * @return boolean
 */
function emailValidation($email) {
	$email = htmlspecialchars(stripslashes(strip_tags($email))); //parse unnecessary characters to prevent exploits
	if (eregi('[a-z||0-9]@[a-z||0-9].[a-z]', $email)) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if a URL exists
 * Copyright: http://neo22s.com/check-if-url-exists-and-is-online-php/
 * 
 * @param string $url
 * @return boolean
 */
function urlExists($url) {
	$url = @parse_url($url);
	if (!$url) return false;

	$url = array_map('trim', $url);
	$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];

	$path = (isset($url['path'])) ? $url['path'] : '/';
	$path .= (isset($url['query'])) ? "?$url[query]" : '';

	// if (isset($url['host']) && $url['host'] != gethostbyname($url['host'])) {
	if (isset($url['host'])) {

		$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
			
		if (!$fp) return false; //socket not opened
			
		fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n"); //socket opened
		$headers = fread($fp, 4096);
		fclose($fp);
			
		if(preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers)){//matching header
			return true;
		}
		else return false;
			
	} // if parse url
	else return false;
}

/**
 * Upload file with FTP
 * 
 * @param string $server_address The FTP server address 
 * @param string $username The FTP server username
 * @param string $password The FTP server password
 * @param string $source_file The path to the file to be uploaded
 * @param string $destination_file The destination file path
 */
function ftpUploadFile($server_address, $username, $password, $source_file, $destination_file) {
	printInfo(__FILE__,"ftp_upload_file($source_file, $destination_file)");
	// set up basic connection
	$conn_id = ftp_connect($server_address);
	// login with username and password
	$login_result = ftp_login($conn_id, $username, $password);
	// check connection
	if ((!$conn_id) || (!$login_result)) {
		// close the FTP stream
		ftp_close($conn_id);
		// print message
// 		end_script(0,"Error: FTP connection has failed! Attempted to connect to ".$server_address." for user ".$username);
		printError(__FILE__,"Error: FTP connection has failed! Attempted to connect to ".$server_address." for user ".$username);
		return false;
	}
	// upload the file
	$upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
	// check upload status
	if (!$upload) {
		// close the FTP stream
		ftp_close($conn_id);
		// print message
// 		end_script(0,"Error: FTP upload has failed");
		printError(__FILE__,"Error: FTP upload has failed");
		return false;
	}
	// check if is not windows
	if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
		// set file permissions
		if(!ftp_chmod($conn_id,0666,$destination_file)) {
			printError(__FILE__,'Error: Cannot change file mod. URL not accessible with browser');
		}
	}
	// close the FTP stream
	ftp_close($conn_id);
	return true;
}

/**
 * Create directory with FTP
 *
 * @param string $server_address FTP server IP address
 * @param string $username FTP server login username
 * @param string $password FTP server login password
 * @param string $directory FTP server directory to create
 * @return boolean
 */
function ftpMakedir($server_address,$username,$password,$directory) {
	printInfo(__FILE__,"ftp_makedir($server_address,$username,$password,$directory)");
	$res = true;
	// set up basic connection
	$conn_id = ftp_connect($server_address);
	// login with username and password
	$login_result = ftp_login($conn_id, $username, $password);
	// check connection
	if ((!$conn_id) || (!$login_result)) {
		// close the FTP stream
		ftp_close($conn_id);
		// print message
		printError(__FILE__,"FTP connection has failed! Attempted to connect to ".$server_address." for user ".$username);
		return false;
	}
	$parts = explode("/",$directory);
	$fullpath = "";
	foreach($parts as $part) {
		if(is_empty($part)) {
			$fullpath .= "/";
			continue;
		}
		$fullpath .= $part."/";
		if(@ftp_chdir($conn_id, $fullpath)) {
			@ftp_cdup($conn_id);
		} else {
			if(@ftp_mkdir($conn_id, $part)) {
				ftp_chdir($conn_id, $part);
			} else {
				printError(__FILE__,"Error creating directory $part");
				$res = false;
			}
		}
	}
	// close the FTP stream
	ftp_close($conn_id);
	return $res;
}

/**
 * Encrypt the string - Apply base64 first and then reverse the string
 * 
 * @param string $str
 * @param int $num_timse
 * @return string
 */
function encodeXT($str, $num_times=5)	{
  	for($i=0;$i<$num_times;$i++) {
  		$encode = base64_encode($str);
    	$reverse = strrev($encode); 
    	$str = $reverse;
  	}
  	return $str;
}

/**
 * Decrypt the string - Reverse the string first and then apply base64
 * 
 * @param string $str
 * @param int $num_times
 * @return string
 */
function decodeXT($str, $num_times=5) {
	for($i=0;$i<$num_times;$i++) {
		$reverse = strrev($str);			
		$decode=base64_decode($reverse); 
		$str = $decode;
	}
	return $str;
}

/**
 * Resize image size to $dest_w x $dest_h
 * 
 * @param String $src_filename
 * @param int $dest_w
 * @param string $dest_dir
 * @return NULL|string
 */
function resizeImg($src_filename,$dest_w,$dest_dir) {
	// get source dimensions
	list($src_w, $src_h) = getimagesize($src_filename);
	// calculate aspect ratio
	$ratio = $src_w/$src_h;
	// calculate destiny height
	$dest_h = $dest_w/$ratio;
	$dest = imagecreatetruecolor($dest_w,$dest_h);
	$path_parts = pathinfo($src_filename);
	$dest_filename =  $dest_dir .'/'.$path_parts['filename'].'_'.$dest_w.'x'.$dest_h.'.'.$path_parts['extension'];
	switch($path_parts['extension']) {
		case 'jpg':
			// Load
			$source = imagecreatefromjpeg($src_filename);
			// Resize
			imagecopyresized($dest, $source, 0, 0, 0, 0, $dest_w, $dest_h, $src_w, $src_h);
			// Output
			if(!imagejpeg($dest,$dest_filename)) return NULL;
			break;
		case 'png':
			// Load
			$source = imagecreatefrompng($src_filename);
			// Resize
			imagecopyresized($dest, $source, 0, 0, 0, 0, $dest_w, $dest_h, $src_w, $src_h);
			// Output
			if(!imagepng($dest,$dest_filename)) return NULL;
			break;
		case 'gif':
			// Load
			$source = imagecreatefromgif($src_filename);
			// Resize
			imagecopyresized($dest, $source, 0, 0, 0, 0, $dest_w, $dest_h, $src_w, $src_h);
			// Output
			if(!imagegif($dest,$dest_filename)) return NULL;
			break;
	}
	return $dest_filename;
}

/**
 * Check image size and aspect ratio.
 * 
 * @param string $image
 * @param int $min_width
 * @param int $min_height
 * @param int $max_width
 * @param int $max_height
 * @return int 0|-1|-2 Valid size range and aspect ratio | Outside size range | Invalid aspect ration
 */
function checkImageSize($image,$min_width,$min_height,$max_width=NULL,$max_height=NULL) {
	// check image size
	list($width,$height) = getimagesize($image);
	if(($width<$min_width) || ($height<$min_height)) return -1;
	if(!is_empty($max_width) && !is_empty($max_height)) {
		if(($width>$max_width) || ($height>$max_height)) return -1;
	}
	// check image ratio
	$image_ratio = $width/$height;
	$ratio = $min_width/$min_height;
	if($image_ratio != $ratio) return -2;
	
	return 0;
}

/**
 * Check if string has HTML in it
 * 
 * @param string $string String to be checked for HTML Tags
 * @return boolean true|false
 */
function is_html($string) {
	if($string != strip_tags($string)) {
		// contains HTML
		return true;
	}
	return false;
}

/**
 * Check the IP location using NetIp server
 * 
 * @param string $ip The IP address
 * @return string Geolocation <town>,<state>,<country>
 */
function geoCheckIP($ip) {
	// array where results will be stored
	$ipInfo=array();
	
	//check, if the provided ip is valid
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		return $ipInfo;
	}
	//contact ip-server
	$response=@file_get_contents('http://www.netip.de/search?query='.$ip);
	if(empty($response)) {
		return false;
	}
	// array containing all regex-patterns necessary to extract ip-geoinfo from page
	$patterns=array();
	$patterns["domain"] = '#Domain: (.*?)&nbsp;#i';
	$patterns["country"] = '#Country: (.*?)&nbsp;#i';
	$patterns["state"] = '#State/Region: (.*?)<br#i';
	$patterns["town"] = '#City: (.*?)<br#i';
	//check response from ipserver for above patterns
	foreach ($patterns as $key => $pattern) {
		// store the result in array
		$ipInfo[$key] = preg_match($pattern,$response,$value) && !empty($value[1]) ? $value[1] : 'not found';
	}

	return $ipInfo["town"]. ", ".$ipInfo["state"].", ".substr($ipInfo["country"], 4);
}

