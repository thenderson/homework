<?php    
require_once('config.php');
                   
// Get POST data
$project_number = strip_tags($_POST['projectnumber']);
$description = array_key_exists('desc', $_POST) ? strip_tags($_POST['desc']) : '';
$magnitude = array_key_exists('mag', $_POST) ? strip_tags($_POST['mag']) : 1;
$promiser = array_key_exists('prom', $_POST) ? strip_tags($_POST['prom']) : 'NULL';
$requester = array_key_exists('req', $_POST) ? strip_tags($_POST['req']) : 'NULL';
$due = array_key_exists('due', $_POST) ? strip_tags($_POST['due']) : '0000-00-00';
$status = strip_tags($_POST['stat']);
$replan = floatval(strip_tags($_POST['replan'])); // = replanned task ID if true, -1 if false

$last_monday = date('Y-m-d', strtotime('last Monday'));
$q_user_metrics = "";
$q_proj_metrics = "";

// Validate input
$description = trim($description);

if ($status == 'OH') {
	$status = 'O';
	$priority = 1;
}
else $priority = 0;

if ($status == 'D') $due = '0000-00-00';

if ($status == 'C') {
	// calculate closed status
	$due_date = DateTime::createFromFormat('Y-m-d', $due);
	$today = new DateTime();
	$foresight = date_diff($today, $due_date)->format('%r%a');
	$status = $foresight < 0 ? 'CL': ($foresight > 13 ? 'C2' : ($foresight > 6 ? 'C1' : 'C0'));
	$closed_on = $today->format('Y-m-d');
	
	$q_user_metrics = "INSERT INTO user_metrics (user_id, date, P, $status)  VALUES($promiser, '$last_monday', 1, 1)
		ON DUPLICATE KEY UPDATE P = P + 1, $status = $status + 1;";						

	$q_proj_metrics = "INSERT INTO project_metrics (project_number, date, P, $status)  VALUES($project_number, '$last_monday', 1, 1)
		ON DUPLICATE KEY UPDATE P = P + 1, $status = $status + 1;";
}
else $closed_on = '0000-00-00';

// Determine task_id for new commitment
if ($replan != -1) { //if this task is a replan of a failed task, increment the task ID by .01
	$floor = floor($replan);
	$ceiling = $floor + .999;

	$stmt = $comm_db->query("SELECT MAX(task_id) AS task_id FROM commitments 
	WHERE project_number = $project_number AND task_id BETWEEN $floor AND $ceiling");
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		echo 'error';
		exit;
	}
	else $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$new_Id = $result[0]['task_id'] + .01;
	if (floor($new_Id) > $floor) $replan = -1; // handles oddball case of a task replanned 99 times by assigning a fresh new task Id
}

if ($replan == -1) {
	$stmt = $comm_db->query("SELECT MAX(task_id) AS task_id FROM commitments WHERE project_number = '$project_number'"); 

	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		echo 'error';
		exit;
	}
	else $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$new_Id = floor($result[0]['task_id']) + 1; 
}

// Insert new commitment into database
$stmt = $comm_db->prepare('INSERT INTO commitments (project_number, task_id, description, magnitude, 
requester, promiser, due_by, closed_on, status, priority_h) VALUES (?,?,?,?,?,?,?,?,?,?)');

if (!$stmt) {
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try {
	$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
	$stmt->bindParam(2, $new_Id, PDO::PARAM_STR);
	$stmt->bindParam(3, $description, PDO::PARAM_STR);
	$stmt->bindParam(4, $magnitude, PDO::PARAM_STR);
	$stmt->bindParam(5, $requester, PDO::PARAM_INT);
	$stmt->bindParam(6, $promiser, PDO::PARAM_INT);
	$stmt->bindParam(7, $due, PDO::PARAM_STR);
	$stmt->bindParam(8, $closed_on, PDO::PARAM_STR);
	$stmt->bindParam(9, $status, PDO::PARAM_STR);
	$stmt->bindParam(10, $priority, PDO::PARAM_INT);
	$stmt->execute();
}             

catch(PDOException $e) {
	trigger_error('Wrong SQL: ' . $e . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

// Retrieve newly created commitment from database and send back to JS
$id = $comm_db->lastInsertId('unique_id');
$stmt = $comm_db->query("SELECT a.unique_id, a.project_number, b.project_shortname, a.task_id, 
	a.description, a.requester, a.promiser, a.due_by, a.priority_h, a.status 
	FROM (SELECT * FROM commitments WHERE unique_id = $id) a, 
	(SELECT project_shortname FROM projects WHERE project_number = '$project_number') b"); 

if (!$stmt) {
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