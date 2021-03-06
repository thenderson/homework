<?php

    /* functions.php
     * Computer Science 50
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
	

	/** from http://www.php.net/manual/en/debugger.php
	* Author : dcz
	* usage :
	* dbug($scalar, $array, $object, $resource, CONSTANT);
	* echo dbug('print'); // actually output the result of all previous calls
	*/

	function dbug() {
		static $output = '', $doc_root;
		$args = func_get_args();
		if (!empty($args) && $args[0] === 'print') {
			$_output = $output;
			$output = '';
			return $_output;
		}
		// do not repeat the obvious (matter of taste)
		if (!isset($doc_root)) {
			$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		}
		$backtrace = debug_backtrace();
		// you may want not to htmlspecialchars here
		$line = htmlspecialchars($backtrace[0]['line']);
		$file = htmlspecialchars(str_replace(array('\\', $doc_root), array('/', ''), $backtrace[0]['file']));
		$class = !empty($backtrace[1]['class']) ? htmlspecialchars($backtrace[1]['class']) . '::' : '';
		$function = !empty($backtrace[1]['function']) ? htmlspecialchars($backtrace[1]['function']) . '() ' : '';
		$output .= "<b>$class$function =&gt;$file #$line</b><pre>";
		ob_start();
		foreach ($args as $arg) {
			if (is_string($arg))
			{
				echo("\n\n********************\n");
				echo ("$arg\n");
				echo("********************\n");
				continue;
			}
			var_dump($arg);
		}
		$output .= htmlspecialchars(ob_get_contents(), ENT_COMPAT, 'UTF-8');
		ob_end_clean();
		$output .= '</pre>';
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
            $location = 'Location: ' . $destination;
        }

        // handle absolute path
        else if (preg_match("/^\//", $destination))
        {
            $isSecure = false;
			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
				$isSecure = true;
			}
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
				$isSecure = true;
			}
			$protocol = $isSecure ? 'https' : 'http';
            $host = $_SERVER["HTTP_HOST"];
            $location = "Location: $protocol://$host$destination";
        }

        // handle relative path
        else
        {
            $isSecure = false;
			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
				$isSecure = true;
			}
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
				$isSecure = true;
			}
			$protocol = $isSecure ? 'https' : 'http';
            $host = $_SERVER["HTTP_HOST"];
            $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
            $location = "Location: $protocol://$host$path/$destination";
        }
		
		http_response_code(302);
		header($location);
		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		
        // exit immediately since we're redirecting anyway
        exit;
    }

    /**
     * Renders template, passing in values.
     */
    function render($template, $values = [])
    {
        // if template exists, render it
        //if (file_exists("./templates/$template"))
		if (true)
        {
            // extract variables into local scope
            extract($values);

            // render header
            require('templates/header.html');
			
			if ($template == 'user_page_form.html') // project page form is never called through this render function, btw.
				require ('templates/header_nav.html');
			
            // render template
            require("templates/$template");

            // render footer
            require('templates/footer.php');
        }

        // else err
        else
        {
            trigger_error("Invalid template: $template", E_USER_ERROR);
        }
    }
?>
