<?php

    // configuration
    require_once('../includes/config.php');
	$lookback = (int) 10; //number of weeks to chart

	/*	RETRIEVE USERNAME LIST */
	$stmt = $comm_db->prepare('
		SELECT project_number, project_name
		FROM projects
		NATURAL JOIN users_projects
		WHERE user_id = :user
		ORDER BY project_number');
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_STR);
		$stmt->execute();		
		$user_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	// load performance metrics for user projects
	$pnums = "";
	foreach ($user_projects as $proj) $pnums = $pnums."'".$proj['project_number']."',";
	$pnums = rtrim($pnums, ',');

	$q = "SELECT project_number, date, PPC, PTA, PTI FROM `project_metrics` 
	WHERE project_number IN ($pnums) AND date BETWEEN date_sub(curdate(), INTERVAL $lookback WEEK) and CURDATE() ORDER BY date";

	$stmt = $comm_db->prepare($q);
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		$stmt->execute();		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	$last_monday = new DateTime(date('Y-m-d', strtotime('last Monday')));

	foreach ($rows as $row) {
		$date = new DateTime($row['date']);
		$weeknum = date_diff($date, $last_monday)->format('%r%a') / 7;
		$metrics[$row['project_number']]['PPC'][$weeknum] = $row['PPC'];
		$metrics[$row['project_number']]['PTA'][$weeknum] = $row['PTA'];
		$metrics[$row['project_number']]['PTI'][$weeknum] = $row['PTI'];
	}
	
	$project_metrics = array();
	
	for ($x = 0; $x < count($metrics); $x++) {
		for ($i=$lookback; $i<1; $i--) {
			$project_metrics[$metrics[$x]['project_number']]['PPC'] = (isset($metrics[$x]['project_number']['PPC'][$i]) ? $metrics[$x]['project_number']['PPC'][$i] : null);
			$project_metrics[$metrics[$x]['project_number']]['PTA'] = (isset($metrics[$x]['project_number']['PTA'][$i]) ? $metrics[$x]['project_number']['PTA'][$i] : null);
			$project_metrics[$metrics[$x]['project_number']]['PTI'] = (isset($metrics[$x]['project_number']['PTI'][$i]) ? $metrics[$x]['project_number']['PTI'][$i] : null);
		}
	}
	
	echo json_encode(array('user_projects'=>$user_projects, 'project_metrics'=>$project_metrics));