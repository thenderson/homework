<!DOCTYPE html>

<html>
    <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="[reliable?], a web-hosted reliable promising system for indivuals and teams.">
		<meta name="keywords" content="reliability, promising, lean, last planner system, commitment, project management">
		<meta name="author" content="Todd Henderson">
		
        <link href="/commgr/public/css/bootstrap.min.css" rel="stylesheet"/>
		<link href="/commgr/public/css/reliable_stylesheet.css" rel="stylesheet"/>
		<script src="/commgr/public/js/respond.min.js"></script>
        <title>reliable</title>
    </head>

    <body>
        <script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="/commgr/public/js/bootstrap.min.js"></script>
		
		<!-- mindmup editable table -->
		<script src="mindmup-editabletable.js"></script>
		<script src="numeric-input-example.js"></script>
		
		<header class="navbar navbar-default" id="top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a href="../" class="navbar-brand">r e l i a b l e</a>
				</div>
				<nav class="collapse navbar-collapse" id="collapse" role="navigation">
					<ul class="nav navbar-nav">
						<li></li>
						<li><a href="index.php">home</a></li>
						<li class="disabled"><a href="settings.php"><span class="glyphicon glyphicon-wrench"></span></a></li>
						<li><a href="logout.php">logout</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
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
				</nav>
			</div>
		</header>