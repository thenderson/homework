<?php

    /* config.php
     * Configures pages. */

    // display errors, warnings, and notices
	ini_set('display_errors', 'On');
    error_reporting(E_ALL);

	// set timezone to US PST
	date_default_timezone_set('America/Los_Angeles');
	
    // requirements
    require('constants.php');
    require('functions.php');

    // enable sessions
    session_start();

	// manage existing sessions as recommended by Gumbo @ 
	// http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes/1270960#1270960
	
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 14,400)) {
    // last request was more than 4 hours ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
	}
	$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

	if (!isset($_SESSION['CREATED'])) {
		$_SESSION['CREATED'] = time();
	} 
	else if (time() - $_SESSION['CREATED'] > 3600) {
		// session started more than 60 minutes ago
		session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
		$_SESSION['CREATED'] = time();  // update creation time
	}

    // require authentication for most pages
    if (!preg_match("{(?:login|logout|register)\.php$}", $_SERVER["PHP_SELF"])) {
        if (empty($_SESSION["id"])) {
            redirect('../public/login.php');
        }
    }

	// open connection to sql database via PDO
	try {
		// connect to database
		$comm_db = new PDO("mysql:dbname=" . DATABASE . ";host=" . SERVER, USERNAME, PASSWORD);
		$comm_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);   // ensure that PDO::prepare returns false when passed invalid SQL
		$comm_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // thow exceptions when errors arise
	}
	catch (Exception $e) {
		// trigger error
		trigger_error($e->getMessage(), E_USER_ERROR);
		exit;
	}
?>
