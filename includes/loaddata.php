<?php     


/*
 * examples/mysql/loaddata.php
 * 
 * This file is part of EditableGrid.
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */


/**
 * This script loads data from the database and returns it to the js
 *
 */
       
require_once('config.php');      
require_once('EditableGrid.php');            

/**
 * fetch_pairs is a simple method that transforms a mysqli_result object in an array.
 * It will be used to generate possible values for some columns.
*/
function fetch_pairs($mysqli,$query){
	if (!($res = $mysqli->query($query)))return FALSE;
	$rows = array();
	while ($row = $res->fetch_assoc()) {
		$first = true;
		$key = $value = null;
		foreach ($row as $val) {
			if ($first) { $key = $val; $first = false; }
			else { $value = $val; break; } 
		}
		$rows[$key] = $value;
	}
	return $rows;
}


// Database connection
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$mysqli->real_connect($config['db_host'],$config['db_user'],$config['db_password'],$config['db_name']); 
                    
// create a new EditableGrid object
$grid = new EditableGrid();

/* 
*  Add columns. The first argument of addColumn is the name of the field in the databse. 
*  The second argument is the label that will be displayed in the header
*/
$grid->addColumn('project', 'project', 'float', NULL, false); 
$grid->addColumn('project', 'project name', 'float', fetch_pairs($mysqli,'SELECT project_number, project_shortname FROM users'),true);
$grid->addColumn('task_id', 'task #', 'string', NULL, false);  
$grid->addColumn('description', 'task description', 'string');  
$grid->addColumn('promiser', 'promised by', 'email' , fetch_pairs($mysqli,'SELECT user_id, name FROM users'),true);  
$grid->addColumn('requester', 'requested by', 'email', fetch_pairs($mysqli,'SELECT user_id, name FROM users'),true);  
$grid->addColumn('due_by', 'due', 'date');                                               
$grid->addColumn('requested_on', 'date requested', 'date', NULL, false);  
$grid->addColumn('status', 'status', 'string');  
$grid->addColumn('type', 'type', 'string');
$grid->addColumn('metric', 'planning', 'string', NULL, false);  
                                                                       
$result = $mysqli->query('SELECT *, date_format(lastvisit, "%d/%m/%Y") as lastvisit FROM demo LIMIT 100');
$mysqli->close();

// send data to the browser
$grid->renderXML($result);

