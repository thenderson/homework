<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);
$project_number = strip_tags($_POST['projectnumber']);
$new_value = strip_tags($_POST['newvalue']);
$column_name = strip_tags($_POST['colname']);
$date_due = strip_tags($_POST['date_due']);

// Update database
switch ($column_name) {
	case 'project_number': // this column isn't currently editable
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
		$new_value = ($new_value == 'true') ? 1 : 0;
		break;

	case 'is_closed':
		if ($new_value == 'false')
		{
			$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
			$new_value = 'O';
		}
		else
		{
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
			
			// calculate closed status
			$due = DateTime::createFromFormat('Y-m-d', $date_due);
			$foresight = date_diff($requested_on, $due)->format('%r%a');
			$now = new DateTime();
			$when_due = date_diff($now, $due)->format('%r%a');
			$new_value = $when_due < 0 ? 'CL': ($foresight > 13 ? 'C2' : ($foresight > 6 ? 'C1' : 'C0'));

			$q="UPDATE commitments SET status = ? WHERE unique_id = ?";
		}
		break;
		
	default:
		// todo: better error handling
		echo 'error';
		exit;
	}

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

// Retrieve newly revised commitment from database and send back to JS
$stmt = $comm_db->query("SELECT a.unique_id, a.project_number, b.project_shortname, a.task_id, 
	a.description, a.requester, a.promiser, a.due_by, a.priority_h, a.status 
	FROM (SELECT * FROM commitments WHERE unique_id = $unique_id) a, 
	(SELECT project_shortname FROM projects WHERE project_number = $project_number) b"); 

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}
else $new_comm = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($new_comm);