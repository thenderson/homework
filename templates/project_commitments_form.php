<?php require 'header.html'; ?>

	<body>
		<div class="container-fluid wrap">
			<div class="col-sm-9 nopadding" id="table_title"><h3><strong>PROJECT COMMITMENTS</strong><i class='fa fa-spinner fa-spin'></i></h3></div>
			<div class="col-sm-3 paginator nopadding" id="user_commitments_paginator"></div>
		</div>
		
		<div class="container-fluid wrap">
			<table id="filterbar">
				<tr>
					<td class="filter filter_task_id">
						<div class="input-group">
							<div class="input-group-addon"><i class='fa fa-filter'></i></div>
							<input class="form-control" type="text" id="filter_id" name="filter" />
						</div>
					</td>
					<td class="filter filter_description">
						<div class="input-group">
							<div class="input-group-addon"><i class='fa fa-filter'></i></div>
							<input class="form-control" type="text" id="filter_desc" name="filter" />
						</div>
					</td>
					<td class="filter filter_requester">
						<div class="input-group">
							<div class="input-group-addon"><i class='fa fa-filter'></i></div>
							<input class="form-control" type="text" id="filter_req" name="filter" />
						</div>
					</td>
					<td class="filter filter_promiser">
						<div class="input-group">
							<div class="input-group-addon"><i class='fa fa-filter'></i></div>
							<input class="form-control" type="text" id="filter_prom" name="filter" />
						</div>
					</td>
					<td class="filter filter_due_by"></td>
					<td class="filter filter_status"></td>
					<td class="filter filter_metric"></td>
					<td class="filter filter_actions"></td>
				</tr>
			</table>
			
			<div id="project_commitments"><i class='fa fa-spinner fa-spin'></i></div>
		</div>
			
		<script src="/commgr/public/js/editablegrid.js"></script>
		<script src="/commgr/public/js/editablegrid_renderers.js" ></script>
		<script src="/commgr/public/js/editablegrid_editors.js" ></script>
		<script src="/commgr/public/js/editablegrid_validators.js" ></script>
		<script src="/commgr/public/js/editablegrid_utils.js" ></script>
		<script src="/commgr/public/js/editablegrid_charts.js" ></script>
		<script src="/commgr/public/js/reliable.js" ></script>
							
		<div id="delete-confirm" title="Delete commitment?" data-role="dialog">   
			<div data-role="content" id="text">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><h4>Deleting commitments cannot be undone. Are you sure?</h4></p>
			</div>
		</div>
		
	</body>
	
<?php require 'footer.php'; ?>

	<script type="text/javascript">
		window.onload = function() {
			
			project_commitments = new CommitmentGrid();
	
			$.ajax({
				url: '../includes/load_project_name.php',
				type: 'POST',
				dataType: 'text',
				data: { p: getparam('project') },
				success: function (response) {
					$('#table_title').html("<h3><strong>PROJECT COMMITMENTS: "+response+"</strong></h3>");},
				error: function(XMLHttpRequest, textStatus, exception) { 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
				async: true
			});
			
			$("#filter_id").keyup(function() { project_comm_grid.filter($(this).val(), [2]); });
			$("#filter_desc").keyup(function() { project_comm_grid.filter($(this).val(), [3]); });
			$("#filter_req").keyup(function() { project_comm_grid.filter($(this).val(), [4]); });
			$("#filter_prom").keyup(function() { project_comm_grid.filter($(this).val(), [5]); });
			
			$("#delete-confirm").dialog({
				resizable: false,
				autoOpen: false,
				height:180,
				modal: true,
				buttons: {
					"Delete": function() {
						$(this).dialog("close");
						CommitmentGrid.DeleteRow($(this).data('id'));
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				}
			});
			
			$.ajax({
				url: '../includes/load_project_commitments.php',
				type: 'POST',
				dataType: "text",
				data: {
					horizon: 30,
					p: getparam('project')
				},
				success: function (response) 
				{
					project_commitments.grid.loadXMLFromString(response); //synchronous function
					project_commitments.grid.tableLoaded();
				},
				error: function(XMLHttpRequest, textStatus, exception) 
				{ 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception); 
				},
				async: true
			});
		}; 
	</script>