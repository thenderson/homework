<?php

    /**
     * functions.php
     *
     * Computer Science 50
     * Problem Set 7
     *
     * Helper functions.
     */

    require_once("constants.php");

    /**
     * Apologizes to user with message.
     */
    function apologize($message)
    {
        render("apology.php", ["message" => $message]);
        exit;
    }

    /**
     * Facilitates debugging by dumping contents of variable
     * to browser.
     */
    function dump($variable)
    {
        require("../templates/dump.php");
        exit;
    }
	
	/**/ 
	function debug( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}
	
	/*  pr() function from http://www.devarticles.in/php/useful-function-to-output-debug-data-in-php/
		To print simple output, where is $output is the output to be printed.
		pr($output) ; //uses print_r by default enclosed in <pre> tags to print output
		pr($output, “var_dump”); //prints output using var_dump enclosed in <pre> tags
		pr($output, ”, true); //returns output ; uses print_r enclosed in <pre> tags
		pr($output, “var_dump”, ‘your@domain.com’); //sends var_dump output enclosed in <pre> tags to email address supplied. Returns as well.
		pr($output, ”, ‘your@domain.com’); //sends print_r output enclosed in <pre> tags to email address supplied. Returns as well.
	*/
	
	if(!function_exists('pr')) 
	{
		function pr($p, $func="print_r", $r=false)
		{
			if(defined('DEBUG_REMOTE_ADDR')&& $_SERVER['REMOTE_ADDR'] != DEBUG_REMOTE_ADDR) return;
			
			if(!function_exists($func))
			{
				die("Debug function {$func} does not exist!");
			}
			
			if(!$func) $func='print_r';
			
			$bt = debug_backtrace();
			$caller = array_shift($bt);
			$file_line = "&lt;strong&gt;" . $caller['file'] . "(line " . $caller['line'] . ")&lt;/strong&gt;\n";
			
			if(!$r)
			{ //if print
				echo '<pre>';
				echo '&lt;!--Debugger Line: ' . $file_line . '--&gt;' . $bt[];
				print_r($file_line);
				$func($p);
				echo '</pre>';
			} 
			else 
			{ //if return
				ob_start();
				echo '&lt;pre&gt;';
				print_r($file_line);
				$func($p);
				echo '&lt;pre&gt;';
				$d = ob_get_contents();
				ob_end_clean();
				
				if(filter_var($r, FILTER_VALIDATE_EMAIL)) 
				{
					$headers = 'From: webmaster@example.com' . "\r\n" .
					'Reply-To: webmaster@example.com' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
					mail($r, 'Debug Output', $d, $headers);
				}
				return $d;
			}
		}
	}

    /**
     * Logs out current user, if any.  Based on Example #1 at
     * http://us.php.net/manual/en/function.session-destroy.php.
     */
    function logout()
    {
        // unset any session variables
        $_SESSION = [];

        // expire cookie
        if (!empty($_COOKIE[session_name()]))
        {
            setcookie(session_name(), "", time() - 42000);
        }

        // destroy session
        session_destroy();
    }

    /**
     * Executes SQL statement, possibly with parameters, returning
     * an array of all rows in result set or false on (non-fatal) error.
     */
    function queryx(/* $sql [, ... ] */)
    {
        // SQL statement
        $sql = func_get_arg(0);

        // parameters, if any
        $parameters = array_slice(func_get_args(), 1);

        // try to connect to database
        static $handle;
        if (!isset($handle))
        {
            try
            {
                // connect to database
                $handle = new PDO("mysql:dbname=" . DATABASE . ";host=" . SERVER, USERNAME, PASSWORD);

                // ensure that PDO::prepare returns false when passed invalid SQL
                $handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
            }
            catch (Exception $e)
            {
                // trigger (big, orange) error
                trigger_error($e->getMessage(), E_USER_ERROR);
                exit;
            }
        }

        // prepare SQL statement
        $statement = $handle->prepare($sql);
        if ($statement === false)
        {
            // trigger (big, orange) error
            trigger_error($handle->errorInfo()[2], E_USER_ERROR);
            exit;
        }

        // execute SQL statement
        $results = $statement->execute($parameters);

        // return result set's rows, if any
        if ($results !== false)
        {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return false;
        }
    }

    /**
     * Redirects user to destination, which can be
     * a URL or a relative path on the local host.
     *
     * Because this function outputs an HTTP header, it
     * must be called before caller outputs any HTML.
     */
    function redirect($destination)
    {
        // handle URL
        if (preg_match("/^https?:\/\//", $destination))
        {
            header("Location: " . $destination);
        }

        // handle absolute path
        else if (preg_match("/^\//", $destination))
        {
            $protocol = (isset($_SERVER["HTTPS"])) ? "https" : "http";
            $host = $_SERVER["HTTP_HOST"];
            header("Location: $protocol://$host$destination");
        }

        // handle relative path
        else
        {
            // adapted from http://www.php.net/header
            $protocol = (isset($_SERVER["HTTPS"])) ? "https" : "http";
            $host = $_SERVER["HTTP_HOST"];
            $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
            header("Location: $protocol://$host$path/$destination");
        }

        // exit immediately since we're redirecting anyway
        exit;
    }

    /**
     * Renders template, passing in values.
     */
    function render($template, $values = [])
    {
        // if template exists, render it
        if (file_exists("../templates/$template"))
        {
            // extract variables into local scope
            extract($values);

            // render header
            require("../templates/header.php");

            // render template
            require("../templates/$template");

            // render footer
            require("../templates/footer.php");
        }

        // else err
        else
        {
            trigger_error("Ooh! Invalid template: $template", E_USER_ERROR);
        }
    }

?>
