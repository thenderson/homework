<?php 
	require '../includes/config.php';
	require 'header.html'; 
	require 'header_nav.html';
	require 'dialogs.html'; 
?>
	</head>
	<body>
		<div class="container-fluid nopadding">
			<div class='container-fluid' id='table_title'>
				<h3><strong>PROJECT COMMITMENTS &nbsp;&nbsp;</strong><i class='fa fa-spinner fa-spin'></i></h3>
			</div>
			<div class="container-fluid">
				<div id="project_commitments" class='grid'>
					<div class="col-sm-12"><br><br><br><i class='fa fa-spinner fa-spin fa-2x text-center'></i><br><br><br></div>
				</div>
				<div>
					<form class='form-inline'>
						<div class="form-group" id="project_commitments_total">total: <i class='fa fa-spinner fa-spin'></i></div>
						<div class="form-group spacer">&nbsp;&nbsp;|&nbsp;&nbsp;page size:&nbsp;</div>
						<div class="form-group">
							<div class="controls">
								<select class="input-sm pg-size" id="comm_page_size" onchange="project_commitments.grid.setPageSize('project_commitmentsPageSize', this.value)">
									<option value=5>5</option>
									<option value=10 selected>10</option>
									<option value=25>25</option>
									<option value=50>50</option>
									<option value=0>all</option>
								</select>
							</div>
						</div>
						<div class="form-group paginator" id="project_commitments_paginator"></div>
					</form>
				</div>
			</div>
		</div>
		<script src="../public/js/editablegrid.js"></script>
		<script src="../public/js/editablegrid_renderers.js" ></script>
		<script src="../public/js/editablegrid_editors.js" ></script>
		<script src="../public/js/editablegrid_validators.js" ></script>
		<script src="../public/js/editablegrid_utils.js" ></script>
		<!--<script src="../public/js/editablegrid_charts.js" ></script>-->
		<script src="../public/js/reliable.js" ></script>		
	</body>	
<?php require 'footer.php'; ?>

<script type="text/javascript">
	window.onload = function() {
		pnum = getparam('project');
		
		// load commitments
		commitments = new CommitmentGrid('project_commitments');

		load_comms = function (horizon, showClosed) {
			if (typeof(horizon) === 'undefined') {
				hor = getCookie('horizon-p');
				horizon = (parseInt(hor, 10) != 'NaN') ? hor : (hor == 'all' ? 'all' : 21);
			}
			
			if (typeof(showClosed)==='undefined') showClosed = (getCookie('showClosed-p') == 'true' ? true : false);
			
			$('#show-closed').prop('checked', showClosed);
			
			setCookie('horizon-p', horizon);
			setCookie('showClosed-p', showClosed);
			
			// load project commitments
			$.ajax({
				url: '../includes/load_project_commitments.php',
				type: 'POST',
				dataType: "text",
				data: {
					horizon: horizon,
					showClosed: showClosed,
					p: pnum
				},
				success: function (response) 
				{
					commitments.grid.loadXMLFromString(response); //synchronous function
					commitments.grid.tableLoaded();
					//$('[id^=commitments_total]').html('total: <strong>'+commitments.grid.getTotalRowCount()+'</strong>');
				},
				error: function(XMLHttpRequest, textStatus, exception) 
				{ 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception); 
				},
				async: true
			});
		};
	
		// configure accordions, sliders, cookies & misc. stuff		
		hor = getCookie('horizon-p');
		horizon = (parseInt(hor, 10) != 'NaN') ? hor : (hor == 'all' ? 'all' : 21);
		setCookie('horizon-p', horizon);
		var weeks = parseInt((horizon == 'all') ? 10 : horizon / 7);
		
		$('#horizon-slider').slider({
			min: 0,
			max: 10,
			step: 1,
			range: 'min',
			value: weeks,
			create: function(event, ui) {
				if (weeks < 1) $('#horizon-text').html('overdue only');
				else if (weeks < 10) $('#horizon-text').html('lookahead: '+weeks+' wk');
				else $('#horizon-text').html('lookahead: all');
			},
			slide: function(event, ui) {
				if (ui.value < 1) $('#horizon-text').html('overdue only');
				else if (ui.value < 10) $('#horizon-text').html('lookahead: '+ui.value+' wk');
				else $('#horizon-text').html('lookahead: all');
			},
			change: function( event, ui ) {
				if (ui.value < 1) {
					horizon = 0;
					$('#horizon-text').html('overdue only');
				}
				else if (ui.value < 10) {
					horizon = ui.value * 7;
					$('#horizon-text').html('lookahead: '+ui.value+' wk');
				}
				else {
					horizon = 'all';
					$('#horizon-text').html('lookahead: all');
				}
				load_comms(horizon);
			}
		});

		show_closed = getCookie('showClosed-p') == 'true' ? true : false;
		$('#show-closed').prop('checked', show_closed);
		setCookie('showClosed-p', show_closed);
		
		$('#show-closed').change(function() {
			load_comms(undefined, this.checked);
		});
			
		commPageSize = getCookie(commitments.name+'PageSize') || 10; 
		$('#comm_page_size').val(commPageSize);
		
		// Load data first time
		load_comms(horizon, show_closed);
		
		// Set-up filters
		commitments.grid.filter(""); //clear any leftover filtering
			
		$("#filter_all").keyup(function() { //one filter to rule them all
			commitments.grid.filter($(this).val());
		});
		
		// populate new commitment requester and promiser select menus with project team members
		$.ajax({
			url: '../includes/load_project_usernames.php',
			type: 'POST',
			dataType: 'JSON',
			data: { p: pnum },
			success: function (proj_users) {
				populate_select_names("#inp-req", proj_users);
				populate_select_names("#inp-prom", proj_users);
			},
			error: function(XMLHttpRequest, textStatus, exception) { 
				alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
			async: true
		});
		
		//load project name, populate table_title & project number selectmenu in New Commitment dialog
		$.ajax({
			url: '../includes/load_project_name.php',
			type: 'POST',
			dataType: 'text',
			data: { p: pnum },
			success: function (response) {
				$('#table_title').html("<h3><strong>PROJECT COMMITMENTS: "+response+"</strong> | #"+pnum+"</h3>");
				$('#inp-proj').append($("<option>").attr('value',response).text(response)).selectmenu('disable');
			},
			error: function(XMLHttpRequest, textStatus, exception) { 
				alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
			async: true
		});
	}; 
</script>