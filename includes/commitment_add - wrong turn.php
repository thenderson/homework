<?php
      
require_once('config.php');         
                      
// Get POST data
$project_number = strip_tags($_POST['project_number']);
$task_id = strip_tags($_POST['task_id']);
$description = strip_tags($_POST['description']);
$promiser = strip_tags($_POST['promiser']);
$requester = strip_tags($_POST['requester']);
$due_by = strip_tags($_POST['due_by']);
$status = strip_tags($_POST['status']);

$stmt = $comm_db->prepare('INSERT INTO commitments (project_number, task_id, description, promiser, requester, due_by, status) VALUES (?, ?, ?, ?, ?, ?, ?)');

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
	$stmt->bindParam(2, $task_id, PDO::PARAM_STR);
	$stmt->bindParam(3, $description, PDO::PARAM_STR);
	$stmt->bindParam(4, $promiser, PDO::PARAM_STR);
	$stmt->bindParam(5, $requester, PDO::PARAM_STR);
	$stmt->bindParam(6, $due_by, PDO::PARAM_STR);
	$stmt->bindParam(7, $status, PDO::PARAM_STR);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

echo 'ok';