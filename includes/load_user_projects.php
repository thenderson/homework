<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     
	
	$lookback = (int) 10; //number of weeks to chart

	/*	RETRIEVE PROJECT LIST & # OF OPEN COMMITMENTS */
	
	$stmt = $comm_db->prepare("
		SELECT 
			a.project_number as project_number_2, 
			a.project_name,
			IF(
				EXISTS(
					SELECT b.user_id, b.project_number
					FROM users_projects b
					WHERE a.project_number = b.project_number
					AND b.user_id = :user), 1,0) as user_belongs,
			(SELECT count(*)
				FROM commitments c
				WHERE c.status IN ('O', '?', 'D', 'NA', NULL)
				AND a.project_number=c.project_number) as num_open
		FROM projects a
		ORDER BY project_number_2");
	
	if (!$stmt)
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_INT);
		$stmt->execute();		
		$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
		
	// load performance metrics for user projects
	$pnums = "";
	foreach ($projects as $proj) $pnums = $pnums."'".$proj['project_number_2']."',";
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
	
	// organize sql data by how long ago it occurred
	$last_monday = new DateTime(date('Y-m-d', strtotime('last Monday')));
	foreach ($rows as $row) {
		$date = new DateTime($row['date']);
		$weeknum = date_diff($date, $last_monday)->format('%r%a') / 7;
		$metrics[$row['project_number']]['PPC'][$weeknum] = $row['PPC'];
		$metrics[$row['project_number']]['PTA'][$weeknum] = $row['PTA'];
		$metrics[$row['project_number']]['PTI'][$weeknum] = $row['PTI'];
	}
	
	// add data to $projects & fill-in missing values so that resulting arrays cover $lookback number of weeks
	// using -1 to stand for an empty value since 0 is a value and null won't work
	foreach ($projects as &$project) {
		$pnum = (string) $project['project_number_2'];
		$project['PPC'] = "";
		$project['PTA'] = "";
		$project['PTI'] = "";
		
		for ($i=$lookback-1; $i>-1; $i--) {
			$project['PPC'] = $project['PPC'] . (isset($metrics[$pnum]['PPC'][$i]) ? $metrics[$pnum]['PPC'][$i] : -1).',';
			$project['PTA'] = $project['PTA'] . (isset($metrics[$pnum]['PTA'][$i]) ? $metrics[$pnum]['PTA'][$i] : -1).',';
			$project['PTI'] = $project['PTI'] . (isset($metrics[$pnum]['PTI'][$i]) ? $metrics[$pnum]['PTI'][$i] : -1).',';
		}
		$project['PPC'] = rtrim($project['PPC'], ',');
		$project['PTA'] = rtrim($project['PTA'], ',');
		$project['PTI'] = rtrim($project['PTI'], ',');
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for PPC & TA
	$grid->addColumn('project_number_2', 'PROJECT #', 'string', NULL, false);
	$grid->addColumn('project_name', 'PROJECT NAME', 'string', NULL, false);
	$grid->addColumn('user_belongs', 'MEMBER OF TEAM', 'boolean', NULL, false);
	$grid->addColumn('num_open', 'OPEN', 'string', NULL, false);
	$grid->addColumn('PPC', 'PPC', 'string', NULL, false);
	$grid->addColumn('PTA', 'PTA', 'string', NULL, false);
	$grid->addColumn('PTI', 'PTI', 'string', NULL, false);

	//render grid
	$grid->renderXML($projects);