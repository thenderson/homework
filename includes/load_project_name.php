<?php
    require_once('../includes/config.php');     
	$project_number = strip_tags($_POST['p']);
	$stmt = $comm_db->prepare('SELECT project_name FROM projects WHERE project_number = ?');
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;}
	
	try {
		$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
		$stmt->execute();		
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);}
		
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR); }

	echo $result[0]['project_name'];