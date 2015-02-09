<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);
$new_value = strip_tags($_POST['newvalue']);
$column_name = strip_tags($_POST['colname']);
$date_due = strip_tags($_POST['date_due']);

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
			//error_log('newvalue: '.$new_value);
			//$date_info = date_parse_from_format('Y.m.d|', $new_value);
			//$new_value = "{$date_info['year']}-{$date_info['month']}-{$date_info['day']}";
			//error_log('dateinfo: '.$date_info.' new newvalue: '.$new_value);
	   }
	   $q="UPDATE commitments SET due_by = ? WHERE unique_id = ?";
	   break;
	   
	case 'priority_h':
		$q='UPDATE commitments SET priority_h = ? WHERE unique_id = ?';
		break;

	case 'is_closed':
		$s = $comm_db->prepare("SELECT requested_on FROM commitments WHERE unique_id = ?");
		
		if (!$s)
		{
			trigger_error('Statement failed : ' . $q->error, E_USER_ERROR);
			echo 'error';
			exit;
		}
		try
		{
			$s->bindParam(1, $unique_id, PDO::PARAM_INT);	
			$s->execute();
		}             
		catch(PDOException $e) 
		{
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
			echo 'error';
			exit;
		} 		
		if (!$s) trigger_error('Statement failed : ' . E_USER_ERROR);
		else 
		{
			$r = $s->fetchAll(PDO::FETCH_ASSOC);
			$requested_on = new DateTime($r[0]['requested_on']) ;
		}
		$due = DateTime::createFromFormat('Y-m-d', $date_due);
		$foresight = date_diff($requested_on, $due)->format('d');
		$now = new DateTime();
		$when_due = date_diff($due, $now)->format('d');
		
		$new_value = ($when_due > 13) ? 'C2' : (($when_due > 6) ? 'C1' : 'C0');
		
		$q="UPDATE commitments SET status = ? WHERE unique_id = ?";
		break;
		
	default:
		// todo: better error handling
		echo 'error';
		exit;
	}

error_log('query: '.$q.' newvalue: '.$new_value);	

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