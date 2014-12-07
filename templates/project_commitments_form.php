<?php require '/commgr/templates/header.html'; ?>

	<body>
		<div class="container-fluid wrap">
			<div><h3><strong>PROJECT COMMITMENTS</strong></h3></div>
			
			<table id="filterbar">
				<tr>
					<td>
						<div class="filter filter_description">
						  <input type="text" id="filter_desc" name="filter" placeholder="filter description"  />
						</div>
					</td>
					<td>
						<div class="filter filter_requester">
						  <input type="text" id="filter_req" name="filter" placeholder="filter requester"  />
						</div>
					</td>
					<td>
						<div class="filter filter_promiser">
						  <input type="text" id="filter_pro" name="filter" placeholder="filter promiser"  />
						</div>
					</td>
				</tr>
			</table>
			
			<div id="project_commitments"></div>
			<div id="project_commitments_paginator"></div>
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
				tableLoaded: function() { this.renderGrid('user_commitments', 'table', 'commitments'); },
				modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
					updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);
				}});
			
			$("#filter_desc").keyup(function() { project_comm_grid.filter($(this).val(), [3]); });
			$("#filter_req").keyup(function() { project_comm_grid.filter($(this).val(), [4]); });
			$("#filter_prom").keyup(function() { project_comm_grid.filter($(this).val(), [5]); });
			
			/*$("#delete-confirm").dialog({
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
			*/
			
			$.ajax({
				url: '../includes/load_data.php',
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