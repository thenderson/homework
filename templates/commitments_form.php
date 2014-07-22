<div class="container">

	<h3 class="padding-8px"><b>C O M M I T M E N T S</b></h3>

	<?php
	$now = new DateTime();
	$comm_count = count($commitments);
	$last_pnum = null;
	
	for ($i=0; $i<$comm_count; $i++)
	{
		$commitment = $commitments[$i];
		$next_pnum = ($i == $comm_count-1) ? null : $commitments[$i+1]['project_number'];
		
		if ($i == 0 || $commitment['project_number'] != $last_pnum) //start a new table
		{ ?>
			<table class="table table-striped table-hover commitments">
				<thead>
					<tr><h4><b><?=$commitment['project_number']." | ".$projects[$commitment['project_number']]?></b></h4></tr>
					<tr>
						<th id="unique_id" class="hidden">u_id</th>
						<th id="task_id" style="width:5%">id #</th>
						<th id="description" style="width:45%">commitment</th>
						<th id="promiser" style="width:14%">promiser</th>
						<th id="requester" style="width:14%">requester</th>
						<th id="due_by" class="text-center" style="width:12%">due by</th>
						<th id="status" class="text-center" style="width:5%">status</th>
						<th id="metric" class="text-right" style="width:5%">metric</th>
					</tr>
				</thead> 
				<tbody><?php
		}
			
		$days_til_due = date_diff(new DateTime($commitment['due_by']), $now)->days;
			
		switch($days_til_due) //choose row formatting by task due date proximity
		{
			case($days_til_due < 0):
				echo('<tr class="danger">');
				break;
			case($days_til_due < 8):
				echo('<tr>');
				break;
			default: //more than one week out
				echo('<tr class="ghost">');
		} ?>
			<td headers="unique_id" contenteditable="false" class="hidden"><?=$commitment['unique_id']?></td>
			<td headers="task_id" contenteditable="false" style="width:5%"><?= $commitment['task_id']?></td>
			<td headers="description" contenteditable="true" style="width:45%;cursor:pointer;"><?= $commitment['description']?></td>

			<td headers="requester" contenteditable="true">
				<select style="width:14%;cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
					<option selected='selected' value="<?=$commitment['requester'].'">'.$username_lookup[$commitment['requester']]?></option>
					<?php 
					foreach ($users as $user) 
					{
						echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
					} ?>
				</select>
			</td>
				
			<td headers="promiser" contenteditable="true">
				<select style="width:14%;cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
					<option selected='selected' value="<?=$commitment['promiser'].'">'.$username_lookup[$commitment['promiser']]?></option>
					<?php 
					foreach ($users as $user) 
					{
						echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
					} ?>
				</select>
			</td>
				
			<td headers="due_by" contenteditable="true" style="width:12%;cursor:pointer;" class="text-center"><?=$commitment['due_by']?></td>
			<td headers="status" contenteditable="true" style="width:5%;cursor:pointer;" class="text-center"><?=$commitment['status']?></td>
			<td headers="metric" contenteditable="false" class="width:5%;text-right"><?=$commitment['metric']?></td>
		</tr> <?php
		
		if ($commitment['project_number'] != $next_pnum) //end table at end of data or different project number
		{ ?>
			</tbody>
			<tfoot>
				<tr>
					<td style="width:10%">total</td>
					<td style="width:10%">ppc</td>
					<td style="width:10%">ta</td>
				</tr>
			</tfoot>
		</table><?php
		}
	$last_pnum = $commitment['project_number'];
	} ?> <!-- close for loop -->
	
	<script>
		$(document).ready(function(){
			$('#commitments').editableTableWidget();
			$('#commitments').editableTableWidget({editor: $('<textarea>')});
			$('#commitments').editableTableWidget({cloneProperties: ['background', 'border', 'outline']});
			
			<!-- mark invalid data -->
			$('table td').on('validate', function(evt, newValue) {
				var cell = $(this);
				var header = cell.attr("headers");
				if (header == "unique_id" || header == "project_num" || header == "project_shortname" || header == 'task_id' || header == 'metric') { 
					return false; // mark cell as invalid 
				}
			});

			<!-- act on changed data -->
			$('table td').on('change', function(evt, newValue) {
				var cell = $(this);
				//var row = cell.parent();
				//var col_num = parseInt(cell.index());
				//var row_num = parseInt(row.index()); 
				var header = cell.attr("headers");
				//var c_class = cell.attr("class");
				var u_id = cell.siblings().first().text();
				var value = cell.text();
					
				//console.log("change detected at: ", u_id, ": ", header, " => ", value);
				
 				if (cell.attr("contenteditable"))
				{
					var response = $.ajax({
						url: '/commgr/includes/change_commitment.php',
						data: {
							u_id: u_id,
							field: header,
							new_value: value
						},
						type: 'POST',
						dataType: 'html'
					});
									
					response.done(function(result) {
						if (result=='success') {
							// flash the changed cell green for 1 second
							cell.addClass('flash-ok');
							var delay = setTimeout(function(){cell.removeClass('flash-ok')}, 500);
							return true;
						} else {
							// flash the changed cell red for 1 second & replace value
							cell.addClass('flash-error');
							var delay = setTimeout(function(){cell.removeClass('flash-error')}, 800);

							console.log(result);
							return false;
						}
					});
						
					response.fail(function(err) {
						console.log( "Request failed: " + err );
						return false;
					});
				}
			});
		});
	</script>
</div> <!-- close container -->