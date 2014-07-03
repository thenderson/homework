<div class="container">

	<h5>C O M M I T M E N T S</h5>

	<div class="alert alert-error hide">
		That would cost too much
	</div>

	<table class="table table-striped" id="commitments">
		<thead>
			<tr>
				<th>project #</th>
				<th>project name</th>
				<th>task #</th>
				<th>description</th>
				<th>promiser</th>
				<th>requester</th>
				<th>due by</th>
				<th>days</th>
				<th>requested on</th>
				<th>status</th>
				<th>type</th>
				<th>metric</th>
			</th>
		</thead>
	
		<tbody>
			<?php

			$now = new DateTime();
			
			foreach ($commitments as $commitment)
			{
				$days_til_due = date_diff($now, new DateTime($commitment["due_by"]))->days;
				
				switch($days_til_due) //choose row formatting by task due date proximity
				{
					case($days_til_due<0):
						?>
						<tr class="danger">
						<?php
						break;
					case($days_til_due<8):
						?>
						<tr class="info">
						<?php
						break;
					default:
						?>
						<tr>
						<?
				}   
				
			?>
					<td><?= $commitment["project"]?></td>
					<td><?= $commitment["project"]?></td>
					<td><?= $commitment["task_id"]?></td>
					<td><?= $commitment["description"]?></td>
					<td><?= $commitment["requester"]?></td>
					<td><?= $commitment["promiser"]?></td>
					<td><?= $commitment["due_by"]?></td>
					<td><?= $days_til_due?></td>
					<td><?= $commitment["status"]?></td>
					<td><?= $commitment["type"]?></td>
					<td><?= $commitment["metric"]?></td>
				</tr>
			<?php      
			}
			?>
		</tbody>
    </table>
	<tfoot>
		<tr>
			
		</tr>
	</tfoot>
	
    <script>
		$('#commitments').editableTableWidget();
		$('#commitments').editableTableWidget({editor: $('<textarea>')});
		$('#commitments').editableTableWidget({
		cloneProperties: ['background', 'border', 'outline']
		});
		
		<!-- mark invalid data -->
		//$('table td').on('validate', function(evt, newValue) {
		//	if (....) { 
		//		return false; // mark cell as invalid 
		//	}
		//});

		<!-- act on changed data -->
		//$('table td').on('change', function(evt, newValue) {
		// do something with the new cell value 
		//if (....) { 
		//	return false; // reject change
		//}
		//});
    </script>
</div>