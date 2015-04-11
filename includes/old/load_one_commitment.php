<?php

// Accepts a task ID and an array of field names matching column names in the commitments & projects tables
// Returns an array of the requested field values for the commitment.

    require_once('../includes/config.php');     
	$unique_id = strip_tags($_POST['id']);
	$params = strip_tags($_POST['params']);
	
	// build query to get requested parameters
	$qa = 'SELECT ';
	$qb = 'SELECT ';
	
	$fields_a = array('project_number', 'description', 'promiser', 'requester', 'status', 'due_by', 'priority_h');
	$fields_b = array('project_name', 'project_shortname');
	$counter_a = 0;
	$counter_b = 0;
	
	foreach ($params as $param) {
		if (in_array($param, $fields_a)) {
			if ($counter_a > 0) $qa += ', ';
			$qa += $param;
			$counter_a ++;
		}
		else if (in_array ($param, $fields_b) {
			if ($counter_b > 0) $qb += ', ';
			$qb += $param;
			counter_b ++;
		}
		else {
			trigger_error('unrecognized field');
			exit;
		}
	}

	$qa += ' FROM commitments WHERE unique_id = ?';
	$qb += ' FROM projects WHERE project_number = ?';
	
	$result1 = array();
	$result2 = array();
	
	if ($counter_a > 0) {
		$stmt = $comm_db->prepare($qa);
			
		if (!$stmt) {
			trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
			exit;}
		
		try {
			$stmt->bindParam(1, $unique_id, PDO::PARAM_STR);
			$stmt->execute();		
			$result1 = $stmt->fetchAll(PDO::FETCH_ASSOC);}
			
		catch(PDOException $e) {
			trigger_error('Wrong SQL: ' . $qa . ' Error: ' . $e->getMessage(), E_USER_ERROR); }
	}
	
	if ($counter_b > 0) {
		$p = $comm_db->prepare('SELECT project_number FROM commitments WHERE unique_id = ?');
		
		if (!$p) {
			trigger_error('Statement failed : ' . $p->error, E_USER_ERROR);
			exit;}
		
		try {
			$p->bindParam(1, $unique_id, PDO::PARAM_STR);
			$p->execute();		
			$result = $p->fetchAll(PDO::FETCH_ASSOC);}
			
		catch(PDOException $e) {
			trigger_error('Wrong SQL: ' . ' Error: ' . $e->getMessage(), E_USER_ERROR); }
		
		$project_number = $result[0]['project_number'];
		
		$stmt = $comm_db->prepare($qb);
		
		if (!$stmt) {
			trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
			exit;}
		
		try {
			$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
			$stmt->execute();		
			$result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);}
			
		catch(PDOException $e) {
			trigger_error('Wrong SQL: ' . $qb . ' Error: ' . $e->getMessage(), E_USER_ERROR); }
	}

echo json_encode(array_merge($result1, $result2));