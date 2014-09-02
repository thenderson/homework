<?php
      
require_once('config.php');         
                      
// Get POST data
$project_number = strip_tags($_POST['projectnumber']);

$stmt = $comm_db->prepare("INSERT INTO commitments (project_number, status) VALUES (?, 'OPEN')");

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

$id = $comm_db->lastInsertId('unique_id');
$new = $comm_db->query("SELECT unique_id, project_number, task_id, description, requester, 
		promiser, due_by, requested_on, status, type, metric 
		FROM commitments WHERE unique_id = $id"); 

if (!$new) trigger_error('Statement failed : ' . E_USER_ERROR);
else $new_comm = $new->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($new_comm);