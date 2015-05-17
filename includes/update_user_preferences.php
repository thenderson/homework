<?php    
require_once('config.php');         
                   
// Get POST data
$pref = strip_tags($_POST['p']);
$new_value = strip_tags($_POST['v']);

// Validate input
if (!in_array($pref, array('pref_show_id', 'pref_show_imp', 'pref_show_mag', 'pref_show_timeline'))) {
	echo 'error';
	exit;
}
if ($new_value == 'true' || $new_value == true) $new_value = 1;
else if ($new_value == 'false' || $new_value == false) $new_value = 0;
else {
	echo 'error';
	exit;
}

// Update user preferences
$q = "UPDATE users SET $pref = $new_value WHERE user_id = :user";

$stmt = $comm_db->prepare($q);

if (!$stmt) {
	trigger_error('Statement failed: ' . $stmt->error, E_USER_ERROR);
	exit;
}
try {
	$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_INT);
	$stmt->execute();
}
catch(PDOException $e) {
	trigger_error('Wrong SQL: ' . $q . ' Error: ' . $e->getMessage(), E_USER_ERROR);
}

echo 'ok';

error_log('');
error_log("UPDATE users SET $pref = $new_value WHERE user_id = $_SESSION['id']");
error_log('');

exit;
