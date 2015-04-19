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
	
	// reorganize sql data into CSV time series for last $lookback number of weeks
	foreach ($metrics as &$metric) {
		$metric['PPC_CSV'] = '';
		$metric['PTA_CSV'] = '';
		$metric['PTI_CSV'] = '';
		
		for ($i=$lookback-1; $i>-1; $i--) {
			$metric['PPC_CSV'] = $metric['PPC_CSV'].(isset($metric['PPC'][$i]) ? $metric['PPC'][$i] : null).',';
			$metric['PTA_CSV'] = $metric['PTA_CSV'].(isset($metric['PTA'][$i]) ? $metric['PTA'][$i] : null).',';
			$metric['PTI_CSV'] = $metric['PTI_CSV'].(isset($metric['PTI'][$i]) ? $metric['PTI'][$i] : null).',';
		}
		
		$metric['PPC_CSV'] = rtrim($metric['PPC_CSV'], ',');
		$metric['PTA_CSV'] = rtrim($metric['PTA_CSV'], ',');
		$metric['PTI_CSV'] = rtrim($metric['PTI_CSV'], ',');
	}
dbug($metrics);
	foreach ($projects as &$project) {
		$pnum = $project['project_number_2'];
		$project['PPC'] = $metrics[$pnum]['PPC_CSV'];
		$project['PTA'] = $metrics[$pnum]['PTA_CSV'];
		$project['PTI'] = $metrics[$pnum]['PTI_CSV'];
	}
dbug($projects);
error_log(dbug('print'));

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for PPC & TA
	$grid->addColumn('project_number_2', 'PROJECT #', 'string', NULL, false);
	$grid->addColumn('project_name', 'PROJECT NAME', 'string', NULL, false);
	$grid->addColumn('user_belongs', 'MEMBER OF TEAM', 'boolean', NULL, false);
	$grid->addColumn('num_open', 'OPEN', 'string', NULL, false);
	$grid->addColumn('ppc', 'PPC', 'string', NULL, false);
	$grid->addColumn('pta', 'PTA', 'string', NULL, false);
	$grid->addColumn('pti', 'PTI', 'string', NULL, false);

	//render grid
	$grid->renderXML($projects);