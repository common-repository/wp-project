<?php
if( isset( $_POST[ 'delete_tasks' ] ) ) {
	$this->delete_tasks( $_POST[ 'task_cb' ] );
}

if( isset( $_POST[ 'submit_task' ] ) ) {
	
	$errors = $this->validate_task( $_POST );
	
	if( empty( $errors ) ) {
	
		if( isset( $_POST[ 'task_id' ] ) ) {
			// Edit existing
			$results = $this->edit_task( $_POST[ 'task_id' ], $_POST[ 'project_id' ], $_POST[ 'task_name' ], $_POST[ 'task_description' ], $_POST[ 'task_priority' ], $_POST[ 'task_time' ], $_POST[ 'task_status' ] );
			
			if( $results === FALSE ) {
				?>
				<div id="message" class="error">
					<p>There were unexpected errors during the update.  Your changes may not have been saved.</p>
				</div>	
				<?php				
			} else { 
				?>
				<div id="message" class="updated fade">
					<p>Your task "<?php echo stripslashes( $_POST[ 'task_name' ] ); ?>" has been updated.</p>
				</div>	
				<?php
			}
			
		} else {
			// Add new
			$results = $this->add_task( $_POST[ 'project_id' ], $_POST[ 'task_name' ], $_POST[ 'task_description' ], $_POST[ 'task_priority' ], $_POST[ 'task_time' ], $_POST[ 'task_status' ] );
			
			if( $results === FALSE ) {
				?>
				<div id="message" class="error">
					<p>There were unexpected errors during the add.  Your changes may not have been saved.</p>
				</div>	
				<?php	
			} else { 
				?>
				<div id="message" class="updated fade">
					<p>Your task "<?php echo stripslashes( $_POST[ 'task_name' ] ); ?>" has been added.</p>
				</div>	
				<?php
			}
			
		}
	} else {
		// The project is invalid, so let's print the error messages
		?>
		<div id="message" class="error">
			<ul>
		<?php foreach( $errors as $error ) { ?>	
		 		<li><?php echo $error; ?></li>
		<?php } ?>
			</ul>
		</div>
		<?php
	
	}
}

$current = $this->is_editing_task();

if( is_array( $errors ) && !empty( $errors ) ) {
	$current[ 'task_id' ] = $_POST[ 'task_id' ];
	$current[ 'project_id' ] = $_POST[ 'project_id' ];
	$current[ 'task_name' ] = $_POST[ 'task_name' ];
	$current[ 'task_description' ] = $_POST[ 'task_description' ];
	$current[ 'task_priority' ] = $_POST[ 'task_priority' ];
	$current[ 'task_time' ] = $_POST[ 'task_time' ];
	$current[ 'task_status' ] = $_POST[ 'task_status' ]; 
}

if( $current !== FALSE ) {
	?>
	<div class="wrap">
		<h2>Edit Task</h2>
	<?php
} else {
?>


<div class="wrap">
	<form name="task-manage" id="task-manage" method="post" action="<?php $this->friendly_page_link( 'tasks' ); ?>">
		<h2>Manage Tasks (<a href="#addtask">add new</a>)</h2>
		<div class="tablenav">
			<div class="alignleft">
				<input name="delete_tasks" id="delete_tasks" class="button-secondary delete" type="submit" value="Delete" />
				<select name="project_select" id="project_select">
					<option value="-1">All Projects</option>
				<?php
				$projects = $this->get_projects( );
				foreach( $projects as $project ) {
					?>
					<option <?php if( $_POST[ 'project_select' ] == $project->project_id ) { echo 'selected'; } ?> value="<?php echo $project->project_id; ?>"><?php echo $project->project_title; ?></option>
					<?php
				}
				?>
				</select>
				<input id="project_select_submit" name="project_select_submit" class="button-secondary delete" value="Filter by Project" type="submit" />
			</div>
			<br class="clear" />
		</div>
		<br class="clear" />
		
		<table class="widefat"> <!-- Start Manage Table -->
			<thead>
				<tr>
					<th class="check-column" scope="col"><input id="selectall" name="selectall" type="checkbox" /></th>
					<th scope="col">Name</th>
					<th scope="col">Description</th>
					<th scope="col">Project</th>
					<th scope="col">Priority</th>
					<th scope="col">Time</th>
					<th scope="col">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if( isset( $_POST[ 'project_select_submit' ] ) && ( $_POST[ 'project_select' ] > 0 ) ) {
					$this->task_rows( $_POST[ 'project_select' ] );
				} else {
					$this->task_rows();
				}
				?>
			</tbody>
		</table> <!-- End Manage Table -->
		
	</form> <!-- End the manage form -->
	<div class="tablenav">
		<br class="clear" />
	</div>
	<br class="clear" />
</div>
<div class="wrap">
	<h2>Add Task</h2>
<?php
}
?>

	<form name="addtask" id="addtask" method="post" action="<?php $this->friendly_page_link( 'tasks' ); echo ( $current !== FALSE ? '&action=edit&id=' . $current[ 'task_id' ] : '' ); ?>">
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="task_name">Name</label></th>
					<td>
						<input id="task_name" name="task_name" type="text" size="30" value="<?php echo $current[ 'task_name']; ?>" /><br />
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="project_id">Project</label></th>
					<td>
						<select name="project_id" id="project_id">
							<option value="-1">N/A</option>
						<?php
						$projects = $this->get_projects( );
						foreach( $projects as $project ) {
							?>
							<option <?php if( $current[ 'project_id' ] == $project->project_id ) { echo 'selected'; } ?> value="<?php echo $project->project_id; ?>"><?php echo $project->project_title; ?></option>
							<?php
						}
						?>
						</select><br />
						<a href="<?php $this->friendly_page_link( 'projects' ); ?>">Add Project</a>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="task_priority">Priority</label></th>
					<td>
						<select name="task_priority" id="task_priority">
						<?php 
						$priorities = $this->priority_options();
						foreach( $priorities as $key => $priority ) {
							?>
							<option <?php if( $current[ 'task_priority' ] == $key ) { echo 'selected'; } ?> value="<?php echo $key; ?>"><?php echo $priority; ?></option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="client_site">Time</label></th>
					<td>
						<input id="task_time" name="task_time" type="text" size="4" value="<?php echo $current[ 'task_time' ]; ?>" /><br />
						Enter the number of hours you've worked on this project in decimal format (i.e. 2.75 = 2 hours and 45 minutes.)
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="task_status">Status</label></th>
					<td>
						<select name="task_status" id="task_priority">
							<option <?php if( $current[ 'task_status' ] == 'Not Started' ) { echo 'selected'; } ?> value="Not Started">Not Started</option>
							<option <?php if( $current[ 'task_status' ] == 'Incomplete' ) { echo 'selected'; } ?> value="Incomplete">Incomplete</option>
							<option <?php if( $current[ 'task_status' ] == 'Complete' ) { echo 'selected'; } ?> value="Complete">Complete</option>
						</select>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="task_description">Description</label>
					<td>
						<textarea name="task_description" id="task_description" style="width:97%;" cols="50" rows="7"><?php echo $current[ 'task_description']; ?></textarea><br />
						Enter a longer description for this task.  You may wish to include specifics on how you can do it or notes about difficulties you've had.
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		if( $current !== FALSE ) {
			?>
			<input type="hidden" name="task_id" id="task_id" value="<?php echo $current[ 'task_id' ]; ?>" />
			<p class="submit">
				<input name="submit_task" id="submit_task" class="button" type="submit" value="Edit Task" />
			</p>
			<?php
		} else {
		?>
		<p class="submit">
			<input name="submit_task" id="submit_task" class="button" type="submit" value="Add Task" />
		</p>
		<?php } ?>
	</form>

</div>
