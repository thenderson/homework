<?php require 'header.html'; ?>

	<body>
		<div class="container-fluid wrap">
			<div class="col-sm-9 nopadding" id="table_title"><h3><strong>PROJECT COMMITMENTS <i class='fa fa-spinner fa-spin'></i></strong></h3></div>
			<div class="col-sm-3 paginator nopadding" id="user_commitments_paginator"></div>
		</div>
		
		<div class="container-fluid wrap">
			<table id="filterbar">
				<tr>
					<td class="filter filter_project_number"></td>
					<td class="filter filter_task_id"></td>
						<div>
							<input class="form-control" type="text" id="filter_id" name="filter" placeholder="<i class='fa fa-filter'>#</i>" />
						</div>
					<td class="filter filter_description">
						<div>
							<input class="form-control" type="text" id="filter_desc" name="filter" placeholder="<i class='fa fa-filter'>#</i>" />
						</div>
					</td>
					<td class="filter filter_requester">
						<div>
							<input class="form-control" type="text" id="filter_req" name="filter" placeholder="<i class='fa fa-filter'>#</i>" />
						</div>
					</td>
					<td class="filter filter_promiser">
						<div>
							<input class="form-control" type="text" id="filter_prom" name="filter" placeholder="<i class='fa fa-filter'>#</i>" />
						</div>
					</td>
					<td class="filter filter_due_by"></td>
					<td class="filter filter_status"></td>
					<td class="filter filter_metric"></td>
					<td class="filter filter_actions"></td>
				</tr>
			</table>
			
			<div id="project_commitments"></div>
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
			
			function getparam(name){
				if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
				return decodeURIComponent(name[1]);
			}
			
			$.datepicker.setDefaults({
			//	dateFormat: "mm/dd/yy",
				numberOfMonths: 2,
				gotoCurrent: true
			});
			
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