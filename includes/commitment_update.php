<?php
 
/*
 * This file is part of EditableGrid.
 * Copyright (c) 2011 Webismymind SPRL Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
      
require_once('config.php');         
                      
// Get all parameters provided by the javascript
$unique_id = strip_tags($_POST['uniqueid']);
$new_value = strip_tags($_POST['newvalue']);
$column_name = strip_tags($_POST['colname']);
$column_type = strip_tags($_POST['coltype']);
                                                
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
$stmt = $comm_db->prepare("UPDATE commitments SET $column_name = ? WHERE unique_id = ?");

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	//$stmt->bindParam(1, $column_name, PDO::PARAM_STR);
	$stmt->bindParam(1, $new_value, PDO::PARAM_STR);
	$stmt->bindParam(2, $unique_id, PDO::PARAM_INT);
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