<?php    
require_once('config.php');         

// functions
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
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);
$project_number = strip_tags($_POST['projectnumber']);
$new_value = strip_tags($_POST['newvalue']);
$old_value = strip_tags($_POST['oldvalue']);
$column_name = strip_tags($_POST['colname']);
$date_due = strip_tags($_POST['date_due']);

$last_monday = date('Y-m-d', strtotime('last Monday'));
$update_stats = 0; // 1 = run $q2 query; 2 = run both $q2 and $q3 queries

error_log('commitment_update started!');
error_log($column_name);

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
			//$date_info = date_parse_from_format('Y.m.d|', $new_value);
			//$new_value = "{$date_info['year']}-{$date_info['month']}-{$date_info['day']}";
	   }
	   $q="UPDATE commitments SET due_by = ? WHERE unique_id = ?";
	   break;
	   
	case 'priority_h':
		error_log('case priority_h');
		
		$q='UPDATE commitments SET priority_h = ? WHERE unique_id = ?';
		$new_value = ($new_value == 'true') ? 1 : 0;
		break;
		
	/*	MAPPING COMMITMENT STATUS CHANGES OLD --> NEW
	   old  new	O	C	D	?	V?	V#
		O		-	1	2	3	X	X		
		C_		4	-	X	X	X	X
		D		5	X	-	6	X	X
		?		7	8	9	-	X	X
		V?		10	X	X	X	-	11
		V#		12	X	X	X	13	-
		
		1. open --> closed: calculate closing status value; increment PPC & TA to project & individual
		2. open --> deferred: set date_due to NULL, set status to D
		3. open --> unknown: set status to ?
		4. closed --> open: decrement PPC & TA to project & individual; set status to O
		5. deferred --> open: set requested_on to current date, set status to O, solicit new date_due
		6. deferred --> unknown: set status to ? [should this be allowed?]
		7. unknown --> open: set status to O
		8. unknown --> closed: (same as 1)
		9. unknown --> deferred: (same as 2)
		10. V? --> open: (same as 4)
		11. V? --> variance: increment V to project & individual; set status to V#
		12. variance --> open: decrement PPC, TA & V to project & individual; set status to 0
		13. variance --> V?: decrement V to project & individual; set status to V_ */
		
	case 'status': 
		error_log('case status');
		switch($old_value) {
			case 'O':
				error_log('case O');
				if ($new_value == 'C') {
					error_log('new value = C');
					// 1. open --> closed: calculate closing status value; increment PPC & TA to project & individual
					$new_value = calc_closed_status($unique_id, $date_due, $comm_db);
					$q='UPDATE commitments SET status = ? WHERE unique_id = ?';
					
					$promiser_q = $comm_db->query("SELECT promiser FROM commitments WHERE unique_id = $unique_id");
					
					if (!$promiser_q) trigger_error('Statement failed : ' . E_USER_ERROR);
					else 
					{
						$promiser_res = $promiser_q->fetchAll(PDO::FETCH_ASSOC);
						$promiser = $promiser_res[0]['promiser'];
					}
					
					$q2 = "IF EXISTS(SELECT 1 FROM user_metrics WHERE `date` = $last_monday AND user_id = $promiser LIMIT 1) THEN
								BEGIN
								UPDATE user_metrics SET P = P + 1, $new_value = $new_value + 1 WHERE user_id = @User AND `date` = $last_monday;
								END;
							ELSE 
								BEGIN
								INSERT INTO user_metrics `date` = $last_monday, P = 1, C0 = 1, user_id = $promiser;
								END;
							END IF;";
					$q3 = "IF EXISTS(SELECT 1 FROM project_metrics WHERE `date` = $last_monday AND project_number = $project_number LIMIT 1) THEN
								BEGIN
								UPDATE project_metrics SET P = P + 1, $new_value = $new_value + 1 WHERE project_number = $project_number, `date` = $last_monday;
								END;
							ELSE
								BEGIN
								INSERT INTO project_metrics `date` = $last_monday, P = 1, $new_value = 1, project_number = $project_number;
								END;
							END IF;";

					$update_stats = 2;
				}
				else if ($new_value == 'D') {
					// 2. open --> deferred: set requested_on and date_due to NULL, set status to D
					$q='UPDATE commitments SET status = ?, date_due = NULL WHERE unique_id = ?'; 
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
					// 4. closed --> open: decrement PPC & TA to project & individual; set status to O
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					
					$q2 = "IF EXISTS(SELECT 1 FROM user_metrics WHERE `date` = $last_monday AND user_id = $promiser LIMIT 1) THEN
								BEGIN
								UPDATE user_metrics SET P = P - 1, $new_value = $new_value - 1 WHERE user_id = @User AND `date` = $last_monday;
								END;
							END IF;";
					$q3 = "IF EXISTS(SELECT 1 FROM project_metrics WHERE `date` = $last_monday AND project_number = $project_number LIMIT 1) THEN
								BEGIN
								UPDATE project_metrics SET P = P + 1, $new_value = $new_value + 1 WHERE project_number = $project_number, `date` = $last_monday;
								END;
							END IF;";
					$update_stats = 2;
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			case 'D':
				if ($new_value == 'O') {
					// 5. deferred --> open: set requested_on to current date, set status to O, solicit new date_due
					$now = new DateTime();
					$q = "UPDATE commitments SET status = ?, requested_on = $now WHERE unique_id = ?";
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
					$q = "UPDATE commitments SET status = ? WHERE unique_id = ?";
				}
				else if ($new_value == 'C') {
					// 8. unknown --> closed: calculate closing status value; increment PPC & TA to project & individual
					$new_value = calc_closed_status($unique_id, $date_due, $comm_db);
					$q='UPDATE commitments SET status = ? WHERE unique_id = ?';
					
					$promiser_q = $comm_db->query("SELECT promiser FROM commitments WHERE unique_id = $unique_id");
					
					if (!$promiser_q) trigger_error('Statement failed : ' . E_USER_ERROR);
					else 
					{
						$promiser_res = $promiser_q->fetchAll(PDO::FETCH_ASSOC);
						$promiser = $promiser_res[0]['promiser'];
					}
					
					$q2 = "IF EXISTS(SELECT 1 FROM user_metrics WHERE `date` = $last_monday AND user_id = $promiser LIMIT 1) THEN
								BEGIN
								UPDATE user_metrics SET P = P + 1, $new_value = $new_value + 1 WHERE user_id = @User AND `date` = $last_monday;
								END;
							ELSE 
								BEGIN
								INSERT INTO user_metrics `date` = $last_monday, P = 1, C0 = 1, user_id = $promiser;
								END;
							END IF;";
					$q3 = "IF EXISTS(SELECT 1 FROM project_metrics WHERE `date` = $last_monday AND project_number = $project_number LIMIT 1) THEN
								BEGIN
								UPDATE project_metrics SET P = P + 1, $new_value = $new_value + 1 WHERE project_number = $project_number, `date` = $last_monday;
								END;
							ELSE
								BEGIN
								INSERT INTO project_metrics `date` = $last_monday, P = 1, $new_value = 1, project_number = $project_number;
								END;
							END IF;";

					$update_stats = 2;
				}
				else if ($new_value == 'D') {
					// 9. unknown --> deferred: set requested_on and date_due to NULL, set status to D
					$q='UPDATE commitments SET status = ?, date_due = NULL WHERE unique_id = ?';
				}
				else {
					echo 'error';
					exit;
				}
				break;
				
			case 'V?':
				if ($new_value == 'O') {
					// 10. V? --> open: decrement PPC & TA to project & individual; set status to O
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					$update_stats = -1;
				}
				else if (preg_match('/^V[1-9]$/', $new_value)) {
					// 11. V? --> variance: increment V to project & individual; set status to V#
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					$update_stats = 1;
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
				if ($new_value == 'O') {
					// 12. variance --> open: decrement PPC, TA & V to project & individual; set status to 0
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					$update_stats = -1;
				}
				else if (preg_match('/^V[1-9]$/', $new_value)) {
					// 13. variance --> V?: update V to project & individual; set status to V_
					$q = 'UPDATE commitments SET status = ? WHERE unique_id = ?';
					$update_stats = -1;
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
		
	default:
		// todo: better error handling
		echo 'error';
		exit;
}

$stmt = $comm_db->prepare($q);
error_log($q);

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
dbug($new_comm); dbug('print');
exit;