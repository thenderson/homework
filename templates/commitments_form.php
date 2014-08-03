<!DOCTYPE html>
<?php $x=null ?>
<html>
    <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="reliablepromising, a web-hosted reliable promising system for individuals and teams.">
		<meta name="keywords" content="reliable promising, reliability, promising, lean, last planner system, commitment, project management">
		<meta name="author" content="Todd Henderson">
		<title>reliable promising</title>
		
		<!-- include default bootsrap css -->
		<link href="/commgr/public/css/bootstrap.min.css" rel="stylesheet"/>
				
		<!-- include javascript and css files for the EditableGrid library -->
		<script src="/commgr/public/js/editablegrid.js"></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_renderers.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_editors.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_validators.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_utils.js" ></script>
		<!-- [DO NOT DEPLOY] <script src="../../editablegrid_charts.js" ></script>  -->
		
		<link rel="stylesheet" href="/commgr/public/css/editablegrid.css" type="text/css" media="screen">

		<!-- include javascript and css files for jQuery, needed for the datepicker and autocomplete extensions -->
		<script src="/commgr/public/extensions/jquery/jquery-1.6.4.min.js" ></script>
		<script src="/commgr/public/extensions/jquery/jquery-ui-1.8.16.custom.min.js" ></script>
		<link rel="stylesheet" href="/commgr/public/extensions/jquery/jquery-ui-1.8.16.custom.css" type="text/css" media="screen">
		
		<!-- include javascript and css files for the autocomplete extension -->
		<script src="/commgr/public/extensions/autocomplete/autocomplete.js" ></script>
		<link rel="stylesheet" href="/commgr/public/extensions/autocomplete/autocomplete.css" type="text/css" media="screen">
		
		<!-- include custom js functions and css -->
		<script src="/commgr/public/js/reliable.js"></script>
		<link href="/commgr/public/css/reliable_stylesheet.css" rel="stylesheet"/>

		<script type="text/javascript">
			window.onload = function() { 
				// you can use "datasource/demo.php" if you have PHP installed, to get live data from the demo.csv file
				editableGrid.onloadJSON("x"); 
			}; 
		</script>
    </head>

    <body>
		<script src="/commgr/public/js/bootstrap.min.js"></script>
		
		<!-- add navbar -->
		<?php require("nav.html");?> 

		<div class="container">

		<!-- TODO
			adjustable column widths
			collapse projects
			date picker
			editable v. not
			sort, filter
			individual view (across projects)
		-->

			<?php
			require("../includes/EditableGrid.php");
			// create grid
			$grid = new EditableGrid();
			
			//declare grid columns
			$grid->addColumn('unique_id', 'U_ID #', 'integer', NULL, false);
			$grid->addColumn('project_number', 'PROJECT #', 'double');
			$grid->addColumn('task_id', 'ID #', 'string', NULL, false);
			$grid->addColumn('description', 'COMMITMENT', 'string');
			$grid->addColumn('promiser','PROMISER','string', $username_lookup);
			$grid->addColumn('requester','REQUESTER','string', $username_lookup);
			$grid->addColumn('due_by','DUE BY','date');
			$grid->addColumn('status','STATUS','string');
			$grid->addColumn('metric','METRIC','string', NULL, false);
			$grid->addColumn('edit','EDIT','string');
			
			//render grid
			$grid->renderJSON($commitments);
			
		?>

			<div id="wrap">
			
				<!-- Feedback message zone -->
				<div id="message"></div>

				<!--  Number of rows per page and bars in chart -->
				<div id="pagecontrol">
					<label for="pagecontrol">Rows per page: </label>
					<select id="pagesize" name="pagesize">
						<option value="8">8</option>
						<option value="16">16</option>
						<option value="32">32</option>
						<option value="64">64</option>
						<option value="128">128</option>
					</select>
					&nbsp;&nbsp;
					<label for="barcount">Bars in chart: </label>
					<select id="barcount" name="barcount">
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="40">40</option>
						<option value="50">50</option>
					</select>	
				</div>
			
				<!-- Grid filter -->
				<label for="filter">Filter :</label>
				<input type="text" id="filter"/>
			
				<!-- Grid contents -->
				<div id="tablecontent"></div>
			
				<!-- Paginator control -->
				<div id="paginator"></div>
			
				<!-- Edition zone (to demonstrate the "fixed" editor mode) -->
				<div id="edition"></div>
				
				<!-- Charts zone -->
				<div id="barchartcontent"></div>
				<div id="piechartcontent"></div>
				
			</div>
		</body>