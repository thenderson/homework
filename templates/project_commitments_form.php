<?php require 'header.html'; ?>

	<body>
		<div class="container-fluid wrap">
			<div class="col-sm-9 nopadding"><h3><strong>PROJECT COMMITMENTS</strong></h3></div>
			<div class="col-sm-3 paginator nopadding" id="user_commitments_paginator"></div>
		</div>
		
		<div class="container-fluid wrap">
			<table id="filterbar">
				<tr>
					<td class="filter filter_project_number"></td>
					<td class="filter filter_task_id"></td>
					<td class="filter filter_description">
						<div>
						  <input type="text" id="filter_desc" name="filter" placeholder="filter description" />
						</div>
					</td>
					<td class="filter filter_requester">
						<div>
						  <input type="text" id="filter_req" name="filter" placeholder="filter requester" />
						</div>
					</td>
					<td class="filter filter_promiser">
						<div>
						  <input type="text" id="filter_prom" name="filter" placeholder="filter promiser" />
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
			
			// load commitments belonging to current project
			project_comm_grid = new EditableGrid("ProjectCommitments", {
				enableSort: true,
				dateFormat: "US",
				pageSize: 15,
				tableRendered:  function() { updatePaginator(this, "project_commitments_paginator"); },
				tableLoaded: function() { this.renderGrid('project_commitments', 'table', 'commitments'); },
				modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
					updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);
				}});
			
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
						datagrid.DeleteRow($(this).data('id'));
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
				{ //process response as xml, then call tableLoaded
					project_comm_grid.loadXMLFromString(response); //synchronous function
					project_comm_grid.tableLoaded();
				},
				error: function(XMLHttpRequest, textStatus, exception) 
				{ 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception); 
				},
				async: true
			});
		}; 
	</script>