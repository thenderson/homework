<div class="container">

	<h3>C O M M I T M E N T S</h3>

	<table class="table table-striped">
		<tr>
			<th>id</th>
			<th>description</th>
			<th>promisor</th>
			<th>requestor</th>
			<th>due</th>
			<th>status</th>
		</th>
		
		<?php

		$now = new DateTime();
		
		foreach ($commitments as $commitment)
		{
			$days_til_due = $now->date_diff(new DateTime($commitment[due_by]);
			
			switch($days_til_due) //choose row formatting by task due date proximity
			{
				case($days_til_due<0):
					?>
					<tr class="danger">
					<?=
					break;
				case($days_til_due<8):
					?>
					<tr class="info">
					<?=
					break;
				default:
					?>
					<tr>
					<?=
			}   
			
		?>

				<td><?= $commitment["task_id"]?></td>
				<td><?= $commitment["description"]?></td>
				<td><?= $commitment["requestor"]?></td>
				<td><?= $commitment["promisor"]?></td>
				<td><?= $commitment["due_by"]?></td>
				<td><?= $commitment["task_id"]?></td>
			</tr>
		<?php      
		}
		?>

    </table>
</div>