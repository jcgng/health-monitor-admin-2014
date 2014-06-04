<?php

/**
 * The Log print functions 
 *
 * @author João Guiomar
 * @since 1.0-b1
 */

/** INCLUDES **/
require_once("Log-1.12.7/Log.php");

/** DEFINES **/
define('LOG_DIR',ABSPATH . 'logs/');
// check if dir exists
if(!is_dir(LOG_DIR)) {
	// create dir
	mkdir(LOG_DIR);
}

/**
 * Print debug message into log
 * 
 * @param string $script The current running script (__FILE__)
 * @param string $message The message to be printed in the log file
 */
function printDebug($script, $message) {
	$basename = basename($script);
	// create Log object
	$l = Log::singleton('file', LOG_DIR . date('d-m-Y').'_trm-network.log',"<$basename>");
	$l->log($message, PEAR_LOG_DEBUG);
	return $message;
}

/**
 * Print info message into log
 *
 * @param string $script The current running script (__FILE__)
 * @param string $message The message to be printed in the log file
 */
function printInfo($script, $message) {
	$basename = basename($script);
	// create Log object
	$l = Log::singleton('file', LOG_DIR . date('d-m-Y').'_trm-network.log',"<$basename>");
	$l->log($message, PEAR_LOG_INFO);
	return $message;
}

/**
 * Print warn message into log
 *
 * @param string $script The current running script (__FILE__)
 * @param string $message The message to be printed in the log file
 */
function printWarn($script, $message) {
	$basename = basename($script);
	// create Log object
	$l = Log::singleton('file', LOG_DIR . date('d-m-Y').'_trm-network.log',"<$basename>");
	$l->log($message, PEAR_LOG_WARNING);
	return $message;
}

/**
 * Print error message into log
 *
 * @param string $script The current running script (__FILE__)
 * @param string $message The message to be printed in the log file
 */
function printError($script, $message) {
	$basename = basename($script);
	// create Log object
	$l = Log::singleton('file', LOG_DIR . date('d-m-Y').'_trm-network.log',"<$basename>");
	$l->log($message, PEAR_LOG_ERR);
	return $message;
}