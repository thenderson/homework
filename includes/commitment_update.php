<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);
$new_value = strip_tags($_POST['newvalue']);
$column_name = strip_tags($_POST['colname']);

// Update database
switch ($column_name) {
	case 'project_number':
		// todo: validate input reflects an existing project number.
		$q="UPDATE commitments SET project_number = ? WHERE unique_id = ?";
		break;
		
	case 'description':
		// todo: check for max length
		$q="UPDATE commitments SET description = ? WHERE unique_id = ?";
		break;
		
	case 'promiser':
		// todo: validate user exists
		$q="UPDATE commitments SET promiser = ? WHERE unique_id = ?";
		break;
		
	case 'requester':
		// todo: validate user exists
		$q="UPDATE commitments SET requester = ? WHERE unique_id = ?";
		break;
		
	case 'due_by':
		// spruce-up the date format
	   if ($new_value === "") 
		 $new_value = NULL;
	   else {
		  $date_info = date_parse_from_format('m/d/Y', $new_value);
		  error_log(var_dump('date from POST: ', $new_value));
		  error_log(var_dump('date_info: ', $date_info));
		  $new_value = "{$date_info['year']}-{$date_info['month']}-{$date_info['day']}";
		  error_log(var_dump('formatted date: ', $new_value));
	   }
	   $q="UPDATE commitments SET due_by = ? WHERE unique_id = ?";
	   break;

	case 'status':
		$q="UPDATE commitments SET status = ? WHERE unique_id = ?";
		break;
	
	default:
		// todo: better error handling
		echo 'error';
		exit;
	}
	
	// ob_start();
	// var_dump($commitments);
	// $contents = ob_get_contents();
	// ob_end_clean();
	// error_log($contents);		
	
$stmt = $comm_db->prepare($q);

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	$stmt->bindParam(1, $new_value, PDO::PARAM_STR);	
	$stmt->bindParam(2, $unique_id, PDO::PARAM_INT);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

echo 'ok';
exit;