<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);

$stmt = $comm_db->prepare('CREATE TEMPORARY TABLE temp_table ENGINE=MEMORY
						SELECT * FROM commitments WHERE unique_id=?;
						UPDATE temp_table SET unique_id=NULL, status=OPEN, metric=NULL;
						INSERT INTO commitments SELECT * FROM temp_table;
						DROP TABLE temp_table';);

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try 
{
	$stmt->bindParam(1, $unique_id, PDO::PARAM_INT);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

echo 'ok';