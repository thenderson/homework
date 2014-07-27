<?php

    /**
     * config.php
     *
     * Configures pages.
     */

    // display errors, warnings, and notices
    ini_set("display_errors", true);
    error_reporting(E_ALL);

	// set timezone to US PST
	date_default_timezone_set('America/Los_Angeles');
	
    // requirements
    require("constants.php");
    require("functions.php");
	//require("EditableGrid.php");

    // enable sessions
    session_start();

    // require authentication for most pages
    if (!preg_match("{(?:login|logout|register)\.php$}", $_SERVER["PHP_SELF"]))
    {
        if (empty($_SESSION["id"]))
        {
            redirect("login.php");
        }
    }
	// open connection to sql database via PDO
	
	try
	{
		// connect to database
		$comm_db = new PDO("mysql:dbname=" . DATABASE . ";host=" . SERVER, USERNAME, PASSWORD);

		
		$comm_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);   // ensure that PDO::prepare returns false when passed invalid SQL
		$comm_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // thow exceptions when errors arise
	}
	catch (Exception $e)
	{
		// trigger error
		trigger_error($e->getMessage(), E_USER_ERROR);
		exit;
	}
?>
