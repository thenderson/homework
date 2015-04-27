<?php    
require_once('config.php');         
                   
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);
$new_value = strip_tags($_POST['newvalue']);
$old_value = strip_tags($_POST['oldvalue']);
$column_name = strip_tags($_POST['colname']);
$date_due = strip_tags($_POST['date_due']);

// Validate input
$new_value = trim($new_value);

$last_monday = date('Y-m-d', strtotime('last Monday'));
$q_user_metrics = "";
$q_proj_metrics = "";

$q = $comm_db->query("SELECT project_number, promiser FROM commitments WHERE unique_id = $unique_id");				
if (!$q) trigger_error('Statement failed : ' . E_USER_ERROR);
else {
	$res = $q->fetchAll(PDO::FETCH_ASSOC);
	$promiser = $res[0]['promiser'];
	$project_number = $res[0]['project_number'];
}

// COMPOSE QUERIES
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
			//$date_info = date_parse_from_format('Y.m.d|', $new_value);
			//$new_value = "{$date_info['year']}-{$date_info['month']}-{$date_info['day']}";
	   }
	   $q="UPDATE commitments SET due_by = ? WHERE unique_id = ?";
	   break;
	   
	case 'priority_h':
		
		$q='UPDATE commitments SET priority_h = ? WHERE unique_id = ?';
		$new_value = ($new_value == 'true') ? 1 : 0;
		break;
		
	/*	MAPPING COMMITMENT STATUS CHANGES OLD --> NEW
	   old  new	O	C	D	?	V?	V#
		O		-	1	2	3	X	X		
		C_		4	-	X	X	X	X
		D		5	5a	-	6	X	X
		?		7	8	9	-	X	X
		V?		X	X	X	X	-	11
		V#		X	X	X	X	13	14
		
		1. open --> closed: calculate closing status value; enter closed_on; increment PPC & TA to project & individual 
		2. open --> deferred: set due_by to NULL, set status to D 
		3. open --> unknown: set status to ?
		4. closed --> open: decrement PPC & TA to project & individual; set status to O, set closed_on to NULL
		5. deferred --> open: set requested_on to current date, set status to O, solicit new due_by
		5a. deferred -> closed: set closing status = C0; set due_by, requested_on & closed_on to current date; increment PPC & TA to project & individual
		6. deferred --> unknown: set status to ?
		7. unknown --> open: set status to O
		8. unknown --> closed: (same as 1)
		9. unknown --> deferred: (same as 2)
		x10. V? --> open: (same as 4) [DEPRECATED: BAD IDEA]
		11. V? --> variance: increment V to project & individual; set status to V#
		x12. variance --> open: decrement PPC, TA & V to project & individual; set status to 0 [DEPRECATED: BAD IDEA]
		13. variance --> V?: decrement V to project & individual; set status to V? 
		14. variance --> variance: decrement old V to project & individual, increment new; set status to V# */
		
	/* TESTING STATUS	(x=basic function restored; X=stat update functional; *=todo listed above
	   old new	O	C	D	?	V?	V#
		O			x	x	x		
		C_		x	
		D		x	x		x
		?		x	x	x
		V?							x
		V#						x	x	*/
		
	case 'status': 
		switch($old_value) {
			case 'O':
				if ($new_value == 'C') {
					// 1. open --> closed: calculate closing status value; set closed_on value; increment PPC & TA to project & individual
					$new_value = calc_closed_status($unique_id, $date_due, $comm_db);
					$q='UPDATE commitments SET status = ?, closed_on = CURDATE() WHERE unique_id = ?';

					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, P, $new_value)  VALUES($promiser, '$last_monday', 1, 1)
						ON DUPLICATE KEY UPDATE P = P + 1, $new_value = $new_value + 1;";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, P, $new_value)  VALUES($project_number, '$last_monday', 1, 1)
						ON DUPLICATE KEY UPDATE P = P + 1, $new_value = $new_value + 1;";

					// INSERT INTO user_metrics (user_id, date, P, C0)  VALUES(1, '2015-03-23', 1, 1)
					// ON DUPLICATE KEY UPDATE P = P + 1, C0 = C0 + 1;	
				}
				else if ($new_value == 'D') {
					// 2. open --> deferred: set requested_on and due_by to NULL, set status to D
					$q='UPDATE commitments SET status = ?, due_by = NULL WHERE unique_id = ?'; 
				}
				else if ($new_value == '?') {
					// 3. open --> unknown: set status to ?
					$q='UPDATE commitments SET status = ? WHERE unique_id = ?';
				}
				else {
					echo 'error';
					exit;
				}
				break;

			case 'C0':
			case 'C1':
			case 'C2':
			case 'CL':
				if ($new_value == 'O') {
					// 4. closed --> open: decrement PPC & TA to project & individual; set status to O, set closed_on to NULL
					$q = 'UPDATE commitments SET status = ?, closed_on = NULL WHERE unique_id = ?';

					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, P, $old_value)  VALUES($promiser, '$last_monday', 0, 0)
						ON DUPLICATE KEY UPDATE P = GREATEST(0, P - 1), $old_value = GREATEST(0, $old_value - 1);";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, P, $old_value)  VALUES($project_number, '$last_monday', 0, 0)
						ON DUPLICATE KEY UPDATE P = GREATEST(0, P - 1), $old_value = GREATEST(0, $old_value - 1);";
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			case 'D':
				if ($new_value == 'O') {
					// 5. deferred --> open: set requested_on to current date, set status to O, solicit new due_by					
					$q = "UPDATE commitments SET status = ?, requested_on = CURDATE() WHERE unique_id = ?";
				}
				else if ($new_value == 'C') {
					// 5a. deferred --> closed: closing status value = C0; enter closed_on; increment PPC & TA to project & individual
					$new_value = 'C0'; // assume zero foresight; sorry!
					$q="UPDATE commitments SET status = ?, due_by = CURDATE(), requested_on = CURDATE(), closed_on = CURDATE() WHERE unique_id = ?";
					
					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, P, $new_value)  VALUES($promiser, '$last_monday', 1, 1)
						ON DUPLICATE KEY UPDATE P = P + 1, $new_value = $new_value + 1;";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, P, $new_value)  VALUES($project_number, '$last_monday', 1, 1)
						ON DUPLICATE KEY UPDATE P = P + 1, $new_value = $new_value + 1;";
				}
				else if ($new_value == '?') {
					// 6. deferred --> unknown: set status to ? [should this be allowed?]
					$q='UPDATE commitments SET status = ? WHERE unique_id = ?';
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			case '?':
				if ($new_value == 'O') {
					// 7. unknown --> open: set status to O
					$q = "UPDATE commitments SET status = ?, closed_on = NULL WHERE unique_id = ?";
				}
				else if ($new_value == 'C') {
					// 8. unknown --> closed: calculate closing status value; increment PPC & TA to project & individual
					$new_value = calc_closed_status($unique_id, $date_due, $comm_db);
					$q='UPDATE commitments SET status = ?, closed_on = CURDATE() WHERE unique_id = ?';
					
					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, P, $new_value)  VALUES($promiser, '$last_monday', 1, 1)
						ON DUPLICATE KEY UPDATE P = P + 1, $new_value = $new_value + 1;";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, P, $new_value)  VALUES($project_number, '$last_monday', 1, 1)
						ON DUPLICATE KEY UPDATE P = P + 1, $new_value = $new_value + 1;";
				}
				else if ($new_value == 'D') {
					// 9. unknown --> deferred: set requested_on and due_by to NULL, set status to D
					$q='UPDATE commitments SET status = ?, due_by = NULL WHERE unique_id = ?';
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			case 'V?':
				// if ($new_value == 'O') {
					// // 10. V? --> open: decrement PPC & TA to project & individual; set status to O, set closed_on to NULL
					// $q = 'UPDATE commitments SET status = ?, closed_on = NULL WHERE unique_id = ?';
					// $update_stats = -1;
				// }
				if (preg_match('/^V[1-9]$/', $new_value)) {
					// 11. V? --> variance: increment V to project & individual; set status to V#
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					
					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, $new_value) VALUES($promiser, '$last_monday', 1)
						ON DUPLICATE KEY UPDATE $new_value = $new_value + 1;";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, $new_value) VALUES($project_number, '$last_monday', 1)
						ON DUPLICATE KEY UPDATE $new_value = $new_value + 1;";
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			case 'V1':
			case 'V2':
			case 'V3':
			case 'V4':
			case 'V5':
			case 'V6':
			case 'V7':
			case 'V8':
			case 'V9':
				// if ($new_value == 'O') {
					// // 12. variance --> open: decrement PPC, TA & V to project & individual; set status to 0
					// $q = 'UPDATE commitments SET status = ?, closed_on = NULL WHERE unique_id = ?';
					// $update_stats = -1;
				// }
				if ($new_value == 'V?') {
					// 13. variance --> V?: decrement old V to project & individual; set status to V_
					$q = 'UPDATE commitments SET status= ? WHERE unique_id = ?';
					
					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, $old_value)  VALUES($promiser, '$last_monday', 0)
						ON DUPLICATE KEY UPDATE $old_value = GREATEST(0, $old_value - 1);";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, $old_value) VALUES($project_number, '$last_monday', 0)
						ON DUPLICATE KEY UPDATE $old_value = GREATEST(0, $old_value - 1);";
				}
				else if (preg_match('/^V[1-9]$/', $new_value)) {
					// 14. variance --> variance: decrement old V to project & individual, increment new; set status to V# */
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					
					$q_user_metrics = "INSERT INTO user_metrics (user_id, date, $new_value, $old_value) VALUES($promiser, '$last_monday', 1, 0)
						ON DUPLICATE KEY UPDATE $new_value = $new_value + 1, $old_value = GREATEST(0, $old_value - 1);";						
	
					$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, $new_value, $old_value) VALUES($project_number, '$last_monday', 1, 0)
						ON DUPLICATE KEY UPDATE $new_value = $new_value + 1, $old_value = GREATEST(0, $old_value - 1);";
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			default:
			// todo: better error handling
				echo 'error';
				exit;
		}
		break;
		
	case 'magnitude':
		$q="UPDATE commitments SET magnitude = ? WHERE unique_id = ?";
		break;
		
	default:
		// todo: better error handling
		echo 'error';
		exit;
}

// UPDATE COMMITMENT DATABASE
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
$q2 = "SELECT a.unique_id, a.project_number, b.project_shortname, a.task_id, 
	a.description, a.requester, a.promiser, a.due_by, a.priority_h, a.status 
	FROM (SELECT * FROM commitments WHERE unique_id = $unique_id) a, 
	(SELECT project_shortname FROM projects WHERE project_number = $project_number) b"; 

$stmt = $comm_db->query($q2); 
	
if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}
else $new_comm = $stmt->fetchAll(PDO::FETCH_ASSOC);

// UPDATE USER_METRICS & PROJECT_METRICS DATABASES, IF NEEDED
if ($q_user_metrics != "" && $q_proj_metrics != "") {
	$stmt_user = $comm_db->query($q_user_metrics);
	
	if (!$stmt_user) {
		trigger_error('Statement failed: ' . $stmt_user->error, E_USER_ERROR);
		echo 'error';
		exit;
	}

	$stmt_proj = $comm_db->query($q_proj_metrics);
		
	if (!$stmt_proj) {
		trigger_error('Statement failed: ' . $stmt_proj->error, E_USER_ERROR);
		echo 'error';
		exit;
	}
}

echo json_encode($new_comm);
exit;

// FUNCTIONS
function calc_closed_status($id, $duedate, $dbase) {
	// lookup when the commitment was first requested
	$s = $dbase->prepare("SELECT requested_on FROM commitments WHERE unique_id = ?");

	if (!$s) {
		trigger_error('Statement failed : ' . $q->error, E_USER_ERROR);
		echo 'error';
		exit;
	}
	try {
		$s->bindParam(1, $id, PDO::PARAM_INT);	
		$s->execute();
	}             
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		echo 'error';
		exit;
	} 		
	if (!$s) trigger_error('Statement failed : ' . E_USER_ERROR);
	else {
		$r = $s->fetchAll(PDO::FETCH_ASSOC);
		$requested_on = new DateTime($r[0]['requested_on']) ;
	}
	
	// calculate closed status
	$due_date = DateTime::createFromFormat('Y-m-d', $duedate);
	$foresight = date_diff($requested_on, $due_date)->format('%r%a');
	$now = new DateTime();
	$when_due = date_diff($now, $due_date)->format('%r%a');
	$closed_status = $when_due < 0 ? 'CL': ($foresight > 13 ? 'C2' : ($foresight > 6 ? 'C1' : 'C0'));
	return $closed_status;
}