<?php    
require_once('config.php');
                   
// Get POST data
$project_number = strip_tags($_POST['projectnumber']);
$description = strip_tags($_POST['desc']);
$promiser = strip_tags($_POST['prom']);
$requester = strip_tags($_POST['req']);
$due = strip_tags($_POST['due']);
$status = strip_tags($_POST['stat']);
$replan = strip_tags($_POST['replan']); // = replanned task ID if true, -1 if false

// Validate input
$description = trim($description);

if ($status == 'OH') {
	$status = 'O';
	$priority = 1;
}
else $priority = 0;

if ($status == 'D') $due = '0000-00-00';

// Determine task_id for new commitment
if ($replan != -1) { //if this task is a replan of a failed task, increment the old task ID
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

	if (floor($new_Id) > $floor) $replan = -1; // handles oddball case of a task replanned 99 times by assigning a new task Id
}

if ($replan = -1) {
	$stmt = $comm_db->query("SELECT MAX(task_id) AS task_id FROM commitments WHERE project_number = $project_number"); 

	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		echo 'error';
		exit;
	}
	else $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$new_Id = $result[0]['task_id'] + 1; 
}

// Insert new commitment into database
$stmt = $comm_db->prepare('INSERT INTO commitments (project_number, task_id, description, requester, promiser, due_by, status, priority_h) VALUES (?,?,?,?,?,?,?,?)');

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
	$stmt->bindParam(2, $new_Id, PDO::PARAM_STR);
	$stmt->bindParam(3, $description, PDO::PARAM_STR);
	$stmt->bindParam(4, $requester, PDO::PARAM_INT);
	$stmt->bindParam(5, $promiser, PDO::PARAM_INT);
	$stmt->bindParam(6, $due, PDO::PARAM_STR);
	$stmt->bindParam(7, $status, PDO::PARAM_STR);
	$stmt->bindParam(8, $priority, PDO::PARAM_INT);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $e . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

// Retrieve newly created commitment from database and send back to JS
$id = $comm_db->lastInsertId('unique_id');
$stmt = $comm_db->query("SELECT a.unique_id, a.project_number, b.project_shortname, a.task_id, 
	a.description, a.requester, a.promiser, a.due_by, a.priority_h, a.status 
	FROM (SELECT * FROM commitments WHERE unique_id = $id) a, 
	(SELECT project_shortname FROM projects WHERE project_number = $project_number) b"); 

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}
else $new_comm = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($new_comm);