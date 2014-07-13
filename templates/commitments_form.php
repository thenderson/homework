<div class="container">

	<h3 class="padding-8px"><b>C O M M I T M E N T S</b></h3>

	<div class="alert alert-error hide">
		That would cost too much
	</div>

	<table class="table table-striped table-hover" id="commitments">
		<thead>
			<tr>
				<th id="unique_id" class="hidden">u_id</th>
				<th id="project_num">project #</th>
				<th id="project_shortname">project</th>
				<th id="task_id">id #</th>
				<th id="description">commitment</th>
				<th id="promiser">promiser</th>
				<th id="requester">requester</th>
				<th id="due_by" class="text-center">due by</th>
				<th id="status" class="text-center">status</th>
				<th id="metric" class="text-right">metric</th>
			</th>
		</thead>
	
		<tbody>
			<?php

			$now = new DateTime();		
			
			foreach ($commitments as $commitment)
			{				
				$days_til_due = date_diff(new DateTime($commitment['due_by']), $now)->days;
				
				switch($days_til_due) //choose row formatting by task due date proximity
				{
					case($days_til_due < 0):
						?>
						<tr class="danger">
						<?php
						break;
					case($days_til_due < 8):
						?>
						<tr>
						<?php
						break;
					default: //more than one week out
						?>
						<tr class="ghost">
						<?
				} ?>
					<td headers="unique_id" contenteditable="false" class="hidden"><?=$commitment['unique_id']?></td>
					<td headers="project_num" contenteditable="false" style="width:8%" class="secondary"><?=$commitment['project_number']?></td>
					<td headers="project_shortname" contenteditable="false" style="width:12%"><?= $projects[$commitment['project_number']]?></td>
					<td headers="task_id" contenteditable="false" style="width:4%" class="secondary"><?= $commitment['task_id']?></td>
					<td headers="description" contenteditable="true" style="width:30% cursor:pointer;"><?= $commitment['description']?></td>

					<td headers="requester" contenteditable="true" style="width:12%">
						<select style="cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
							<option selected='selected' value="<?=$commitment['requester'].'">'.$username_lookup[$commitment['requester']]?></option>
							<? foreach ($users as $user) 
							{
								echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
							} ?>
						</select>
					</td>
					
					<td headers="promiser" contenteditable="true" style="width:12%">
						<select style="cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
							<option selected='selected' value="<?=$commitment['promiser'].'">'.$username_lookup[$commitment['promiser']]?></option>
							<? foreach ($users as $user) 
							{
								echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
							} ?>
						</select>
					</td>
					
					<td headers="due_by" contenteditable="true" style="width:12%;cursor:pointer;" class="text-center"><?= $commitment['due_by']?></td>
					<td headers="status" contenteditable="true" style="width:5%;cursor:pointer;" class="text-center"><?= $commitment['status']?></td>
					<td headers="metric" contenteditable="false" style="width:5%" class="text-right"><?= $commitment['metric']?></td>
				</tr>
			<?php } ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
	
	<script>
		$(document).ready(function(){
			$('#commitments').editableTableWidget();
			$('#commitments').editableTableWidget({editor: $('<textarea>')});
			$('#commitments').editableTableWidget({cloneProperties: ['background', 'border', 'outline']});
			
			<!-- mark invalid data -->
			//$('table td').on('validate', function(evt, newValue) {
			//	if (....) { 
			//		return false; // mark cell as invalid 
			//	}
			//});

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
					
				console.log("change detected at: ", u_id, " => ", value);
				
 				if (cell.attr("contenteditable"))
				{
					var response = $.ajax({
						url: '/includes/change_commitment.php',
						data: {
							u_id: u_id,
							field: header,
							new_value: value
						},
						type: 'POST',
						dataType: 'text'
					});
									
					response.done(function(result) {
						if (result=='success') {
							// flash the changed cell green for 1 second
							cell.addClass('flash-green').delay(250).fadeOut(fast).removeClass('flash-green');
							return true;
						} else {
							// flash the changed cell red for 1 second & replace value
							cell.addClass('flash-red').delay(250).fadeOut(fast).removeClass('flash-red');
							alert("Error: " + result);
							return false;
						}
					});
						
					response.fail(function(err) {
						alert( "Request failed: " + err );
						return false;
					});
				}
			});
		});
	</script>
</div> <!-- close container -->