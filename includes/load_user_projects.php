<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

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
					AND b.user_id = 3), 1,0) as user_belongs,
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
		$stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
		$stmt->execute();		
		$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
		
	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for PPC & TA
	$grid->addColumn('project_number_2', 'PROJECT #', 'string', NULL, false);
	$grid->addColumn('project_name', 'PROJECT NAME', 'string', NULL, false);
	$grig->addColumn('user_belongs', 'MEMBER OF TEAM', 'boolean');
	$grid->addColumn('num_open', 'OPEN', 'string', NULL, false);
	$grid->addColumn('ppc', 'PPC', 'string', NULL, false);
	$grid->addColumn('ta', 'TA', 'string', NULL, false);

	//render grid
	$grid->renderXML($projects);