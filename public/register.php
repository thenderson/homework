<?php

    // configuration
    require("../includes/config.php");

    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // TODO
        //validate form data: duplicate username, duplicate email, crappy password
        if (empty($_POST["username"]) || empty($_POST["password"]))
        {
            // ID-10-T error
            apologize("Error ID10t: missing username and/or password.");
        }
        else if ($_POST["password"] != $_POST["password-conf"])
        {
            // ID-10-T error
            apologize("Error ID10t: password & confirmation don't match.");
        }
        else //looks good so far
        {
			$name = strip_tags($_POST['name']);
			$company = strip_tags($_POST['company']);
			$username = strip_tags($_POST['username']);
			$hash = crypt($_POST['password']);
			$email = strip_tags($_POST['email']);
            
			$stmt = $comm_db->prepare("INSERT INTO users (name, company, username, hash, email, pref_alerts, pref_reports) 
				VALUES(?, ?, ?, ?, ?, 'no_alerts', 'no_reports')");

			if (!$stmt)
			{
				trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
				echo 'error';
				exit;
			}

			try
			{
				$stmt->bindParam(1, $name, PDO::PARAM_STR);
				$stmt->bindParam(2, $company, PDO::PARAM_STR);	
				$stmt->bindParam(3, $username, PDO::PARAM_STR);	
				$stmt->bindParam(4, $hash, PDO::PARAM_STR);	
				$stmt->bindParam(5, $email, PDO::PARAM_STR);	
				$stmt->execute();
			}             

			catch(PDOException $e) 
			{
				if ($e->getCode()== 23000)
				{
					apologize('Username already taken. Select a unique username.');
				}
				else
				{
					trigger_error('SQL Error: ' . $e->getMessage(), E_USER_ERROR);
					exit;
				}
			}

			$id = $comm_db->lastInsertId();
			$_SESSION['id'] = $id;
			redirect('../index.php');
        }
    }
    else
    {
        // else render form
        render('register_form.php', ["title" => "Register"]);
    }
?>
