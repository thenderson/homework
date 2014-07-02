<div class="container">

	<h5>C O M M I T M E N T S</h5>

	<table class="table table-striped">
		<tr>
			<th>id</th>
			<th>description</th>
			<th>promiser</th>
			<th>requester</th>
			<th>due</th>
			<th>status</th>
		</th>
		
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

				<td><?= $commitment["task_id"]?></td>
				<td><?= $commitment["description"]?></td>
				<td><?= $commitment["requester"]?></td>
				<td><?= $commitment["promiser"]?></td>
				<td><?= $commitment["due_by"]?></td>
				<td><?= $commitment["status"]?></td>
			</tr>
		<?php      
		}
		?>

    </table>
</div>