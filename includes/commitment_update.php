<?php
 
/*
 * This file is part of EditableGrid.
 * Copyright (c) 2011 Webismymind SPRL Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
      
require_once('config.php');         
                      
// Get all parameters provided by the javascript
$unique_id = $mysqli->real_escape_string(strip_tags($_POST['uniqueid']));
$new_value = $mysqli->real_escape_string(strip_tags($_POST['newvalue']));
$column_name = $mysqli->real_escape_string(strip_tags($_POST['colname']));
$column_type = $mysqli->real_escape_string(strip_tags($_POST['coltype']));
                                                
// spruce-up the date format
if ($column_type == 'date') {
   if ($new_value === "") 
  	 $new_value = NULL;
   else {
      $date_info = date_parse_from_format('d/m/Y', $value);
      $new_value = "{$date_info['year']}-{$date_info['month']}-{$date_info['day']}";
   }
}
                      
// TODO: other input validation needs to go here.

// Update database
$stmt = $comm_db->prepare("UPDATE commitments SET ".$colname." = ? WHERE unique_id = ?");

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	$stmt->bind_param(1, $new_value, $column_name, PDO::PARAM_STR);
	$stmt->bind_param(2, $new_value, $unique_id, PDO::PARAM_INT);
	$stmt->execute();
	$stmt->close();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

echo 'ok';