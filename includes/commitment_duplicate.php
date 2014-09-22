<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueId']);

// Determine task_id for new commitment
$stmt = $comm_db->query("SELECT MAX(task_id) AS task_id FROM commitments WHERE project_number = $project_number"); 

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}
else $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$new_Id = $result[0]['task_id'] + 1; 

// Duplicate selected record in database
$stmt = $comm_db->prepare('INSERT INTO `commitments` ( project_number, task_id, description, requester, promiser, due_by, status, type, metric )
							SELECT project_number, ?, description, requester, promiser, due_by, "OPEN", type, NULL
							FROM `commitments` WHERE `unique_id`=?');

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	$stmt->bindParam(1, $new_Id, PDO::PARAM_STR);
	$stmt->bindParam(2, $unique_id, PDO::PARAM_INT);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

// Retrieve newly created commitment from database and send back to JS
$id = $comm_db->lastInsertId('unique_id');
$stmt = $comm_db->query("SELECT unique_id, project_number, task_id, description, requester, 
		promiser, due_by, requested_on, status, type, metric 
		FROM commitments WHERE unique_id = $id"); 

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}
else $new_comm = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($new_comm);
