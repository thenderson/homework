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

	$q = "SELECT project_number, date, PPC, PTA, PTI, V1, V2, V3, V4, V5, V6, V7, V8, V9 FROM `project_metrics` 
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
	
	// organize sql data by how long ago it occurred & sum variances over the period $lookback
	$last_monday = new DateTime(date('Y-m-d', strtotime('last Monday')));

	foreach ($rows as $row) {
		$pnum = (string) $row['project_number'];
		$date = new DateTime($row['date']);
		$weeknum = date_diff($date, $last_monday)->format('%r%a') / 7;
		$metrics[$pnum]['PPC'][$weeknum] = $row['PPC'];
		$metrics[$pnum]['PTA'][$weeknum] = $row['PTA'];
		$metrics[$pnum]['PTI'][$weeknum] = $row['PTI'];
		
		for ($i = 1; $i<10; $i++) { //sum variances for each project across $lookback
			$v = 'V'.$i;
			$metrics[$pnum][$v] = isset($metrics[$pnum][$v]) ? $metrics[$pnum][$v] + $row[$v] : $row[$v];
		}
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
		
		//$variances = ['V1'=>$metrics[$pnum]['V1'], 'V2'=>$metrics[$pnum]['V2'], 'V3'=>$metrics[$pnum]['V3'], 
		//'V4'=>$metrics[$pnum]['V4'], 'V5'=>$metrics[$pnum]['V5'], 'V6'=>$metrics[$pnum]['V6'], 
		//'V7'=>$metrics[$pnum]['V7'], 'V8'=>$metrics[$pnum]['V8'], 'V9'=>$metrics[$pnum]['V9']];
		
		for ($i = 1; $i < 10; $i++) { // build key-value array of variances
			$v = "'V$i'";
			$variances[$v] = isset($metrics[$pnum][$v]) ? $metrics[$pnum][$v] : 0];
		}
		
		$project['V'] = json_encode($variances);
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
	$grid->addColumn('V', 'Variance', 'string', NULL, false);

	//render grid
	$grid->renderXML($projects);