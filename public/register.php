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
            apologize("Error ID-10t: missing username and/or password.");
        }
        else if ($_POST["password"] != $_POST["password-conf"])
        {
            // ID-10-T error
            apologize("Error ID-10t: password & confirmation don't match.");
        }
        else
        {
            // register user in database
            $result = query("INSERT INTO users (name, company, username, hash, email, pref_alerts, pref_reports) VALUES(?, ?, ?, ?, ?, ?, ?)", 
				$_POST["name"], $_POST["company"], $_POST["username"], crypt($_POST["password"]), $_POST["email"], "no_alerts", "no_reports");
            
            if ($result === false)
            {
                apologize("Crap. Something went wrong ... duplicate username?");
            }
            else //success
            {
                $rows = query("SELECT LAST_INSERT_ID() AS id");
                $id = $rows[0]["id"];
                $_SESSION = $id;
				error_log("successful registration; session id ".$id);
                redirect("/commgr/public/index.php");
            }
        }
    }
    else
    {
        // else render form
        render("register_form.php", ["title" => "Register"]);
    }

?>
