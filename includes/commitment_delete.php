<?php
 
/*
 * adapted from ...
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
      
require_once('config.php');         

// Get all parameter provided by the javascript
$id = $mysqli->real_escape_string(strip_tags($_POST['unique_id']));

// This very generic. So this script can be used to update several tables.
$return=false;
if ( $stmt = $mysqli->prepare("DELETE FROM commitments WHERE unique_id = ?")) {
	$stmt->bind_param("i", $unique_id);
	$return = $stmt->execute();
	$stmt->close();
}             
$mysqli->close();        

echo $return ? "ok" : "error";

      

