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
		
        <div class="container-fluid">
			<div class="row">
				<nav class="navbar navbar-default" role="navigation">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-type="collapse" data-target="#collapse">
							<span class="sr-only">toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
					</div>
					<div class="collapse navbar-collapse" id="collapse">
						<ul class="nav navbar-nav">
							<li><a href="index.php">home</a></li>
							<li class="disabled"><a href="settings.php"><span class="glyphicon glyphicon-wrench"></span></a></li>
							<li><a href="logout.php">logout</a></li>
								<?php
									if (empty($_POST["username"]))
									{ ?>
										<li class="navbar-text navbar-right">guest</li>
									<?php
									}
									else
									{ ?>
										<li class="navbar-text navbar-right"><?=$_POST["username"]?></li>
									<?php
									} ?>
						</ul>
					</div>
				</nav> <!--close nav-->
			</div> <!--close row-->
		</div> <!--close container-->
