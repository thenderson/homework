<!DOCTYPE html>

<html>
    <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
        <link href="/commgr/public/css/bootstrap.min.css" rel="stylesheet"/>
		<link href="/commgr/public/css/reliable_stylesheet.css" rel="stylesheet"/>
		<script src="/commgr/public/js/respond.min.js"></script>

        <title>reliable</title>
    </head>

    <body>
        <script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="/commgr/public/js/bootstrap.min.js"></script>
		
        <div class="container">
		
			<header class="row">
				<div class="col-lg-2 col-lg-offset-2 col-md-2 col-md-offset-2 col-sm-2 col-sm-offset-2 col-xs-2 col-xs-offset-1 col-lg-push-8 col-md-push-8 col-sm-push-8 col-xs-push-9">
					<img src="/commgr/public/img/BA Logo Blue.jpg" alt="">
				</div>
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-9 col-lg-pull-4 col-md-pull-4 col-sm-pull-4 col-xc-pull-3">
					<h3>reliable: commitment tracking for project teams</h3>
				</div>
			</header>
			
            <div> <!--button bar-->
				<form class="navbar-form navbar-left">
					<div class="form-group">
						<form action="index.php">
							<input type="submit" class="btn btn-success" value = "commitments">
						</form>
					</div>
					<div class="form-group">
						<form action="settings.php">
							<input type="submit" class="btn btn-default" value = "settings">
						</form>
					</div>
					<div class="form-group">
						<form action="logout.php">
							<input type="submit" class="btn btn-default" value = "logout">
						</form>
					</div>
				</form>
			
                <table class="center" id="buttonbar">
                <tr>
                    <td>
                        <form action="index.php">
                            <input type="submit" value = "commitms">
                        </form> 
                    </td>
                    <td>  
                        <form action="settings.php">
                            <input type="submit" value = "setgs">
                        </form>  
                    </td> 
                    <td>  
                        <form action="logout.php">
                            <input type="submit" value = "logt">
                        </form>  
                    </td> 
                </tr>
                </table>
            </div>
		</div> <!--close container-->
