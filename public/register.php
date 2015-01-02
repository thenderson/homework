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
        else
        {
            // register user in database
			$hash = crypt($_POST('password'));
            $result = $comm_db->query("INSERT INTO users (name, company, username, hash, email, pref_alerts, pref_reports) 
				VALUES({$_POST["name"]}, {$_POST["company"]}, {$_POST["username"]}, {$hash}, {$_POST["email"]}, 'no_alerts', 'no_reports')");
            
            if ($result === false)
            {
                apologize("Crap. Something went wrong ... duplicate username?");
            }
            else //success
            {
                $rows = $comm_db('SELECT LAST_INSERT_ID() AS user_id');
                $id = $rows[0]["user_id"];
                $_SESSION["id"] = $id;		
                redirect('../index.php');
            }
        }
    }
    else
    {
        // else render form
		error_log('rendering registration form');
        render('register_form.php', ["title" => "Register"]);
    }

?>
