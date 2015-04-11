<?php

// Accepts a task ID, returns an array of all field values for the commitment.

    require_once('../includes/config.php');     
	$unique_id = strip_tags($_POST['id']);
	
	// load project number, requester & promiser
	$q1 = 'SELECT project_number, requester, promiser FROM commitments WHERE unique_id = ?';
	
	if (!($stmt = $comm_db->prepare($q1))) {
		trigger_error('Statement failed : ' . $p->error, E_USER_ERROR);
		exit;}
	
	try {
		$stmt->bindParam(1, $unique_id, PDO::PARAM_STR);
		$stmt->execute();		
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);}
		
	catch(PDOException $e) {trigger_error('Wrong SQL: ' . ' Error: ' . $e->getMessage(), E_USER_ERROR);}
	
	$project_number = $result[0]['project_number'];
	$requester = $result[0]['requester'];
	$promiser = $result[0]['promiser'];
	
	// load commitment data from three tables
	$q2 = 
	"SELECT a.unique_id, a.project_number, b.project_shortname, a.task_id, a.description, a.requester, c.requester_name, a.promiser, d.promiser_name, a.due_by, a.priority_h, a.status FROM 
	(SELECT unique_id, project_number, task_id, description, requester, promiser, due_by, priority_h, status FROM commitments WHERE unique_id = $unique_id) a, 
	(SELECT project_shortname FROM projects WHERE project_number = $project_number) b,
	(SELECT name as requester_name FROM users WHERE user_id = $requester) c,
	(SELECT name as promiser_name FROM users WHERE user_id = $promiser) d"; 
	
	// TODO: improve this query!
	
	if (!($stmt = $comm_db->prepare($q2))) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;}
	
	try {
		$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
		$stmt->execute();		
		$result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);}
		
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $qb . ' Error: ' . $e->getMessage(), E_USER_ERROR);}

echo json_encode($result2[0]);