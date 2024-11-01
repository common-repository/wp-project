<?php
// All processing logic happens here

// We'll check for deletes
if( isset( $_POST[ 'delete_projects' ] ) ) {
	$this->delete_projects( $_POST[ 'project_cb' ] );
}

// We'll check for add or edit
if( isset( $_POST[ 'submit_project' ] ) ) {
	// We've got a submission, let's figure out what to do
	
	$errors = $this->validate_project( $_POST );
	
	if( empty( $errors ) ) {
		
		// The $_POST array is already escaped, so that's why they aren't escaped here
		if( isset( $_POST[ 'project_id' ] ) ) {
			// We're editing an existing project
			
			$result = $this->edit_project( $_POST[ 'project_id' ], $_POST[ 'project_title' ], $_POST[ 'client_id' ], $_POST[ 'project_description' ] );
			
			if( $result === FALSE ) {
				?>
				<div id="message" class="error">
					<p>There was an error updating the project.  Your changes may not have been saved.  Please try again.</p>
				</div>				
				<?
			} else {
				?>
				<div id="message" class="updated fade">
					<p>Your project "<?php echo stripslashes( $_POST[ 'project_title' ] ); ?>" has been updated.</p>
				</div>				
				<?
			}
			
		} else {
			// We're adding a new project
			
			$result = $this->add_project( $_POST[ 'project_title' ], $_POST[ 'client_id' ], $_POST[ 'project_description' ] );
			
			if( $result === FALSE ) {
				?>
				<div id="message" class="error">
					<p>There was an error adding the project.  Your changes may not have been saved.  Please try again.</p>
				</div>				
				<?
			} else {
				?>
				<div id="message" class="updated fade">
					<p>Your project "<?php echo stripslashes( $_POST[ 'project_title' ] ); ?>" has been added.</p>
				</div>				
				<?
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
		
		$current[ 'project_id' ] = $_POST[ 'project_id' ];
		$current[ 'project_title' ] = $_POST[ 'project_title' ];
		$current[ 'project_description' ] = $_POST[ 'project_description' ];
		$current[ 'client_id' ] = $_POST[ 'client_id' ];
		
	}
}

$current = $this->is_editing_project();

// Is a valid project being edited? If so, then let's show the edit screen.
if( $current !== FALSE ) {
	?>
<div class="wrap">
	<h2>Edit Project</h2>
	<?php
} else {
?>
<div class="wrap">
	<form name="project-manage" id="project-manage" method="post" action="<?php $this->friendly_page_link( 'projects' ); ?>">
		<h2>Manage Projects (<a href="#addproject">add new</a>)</h2>
		<div class="tablenav">
			<div class="alignleft">
				<input name="delete_projects" id="delete_projects" class="button-secondary delete" type="submit" value="Delete" />
			</div>
			<br class="clear" />
		</div>
		<br class="clear" />
		
		<table class="widefat"> <!-- Start Manage Table -->
			<thead>
				<tr>
					<th class="check-column" scope="col"><input id="selectall" name="selectall" type="checkbox" /></th>
					<th scope="col">Name</th>
					<th scope="col">Client</th>
					<th scope="col">Description</th>
				</tr>
			</thead>
			<tbody>
				<?php $this->project_rows(); ?>
			</tbody>
		</table> <!-- End Manage Table -->
		
	</form> <!-- End the manage form -->
	<div class="tablenav">
		<br class="clear" />
	</div>
	<br class="clear" />
</div>

<div class="wrap">
	<h2>Add Project</h2>
<?php
}
?>
	<form name="addproject" id="addproject" method="post" action="<?php $this->friendly_page_link( 'projects' ); echo ( $current !== FALSE ? '&action=edit&id=' . $current[ 'project_id' ] : '' ); ?>">
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="project_title">Title</label></th>
					<td>
						<input id="project_title" name="project_title" type="text" size="30" value="<?php echo $current[ 'project_title']; ?>" /><br />
						Choose a project title that succinctly describe this project in 200 characters or less.
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="client_id">Client</label></th>
					<td>
						<select class="postform" id="client_id" name="client_id">
							<option value="-1">N/A</option>
						<?php
						foreach( $this->get_clients() as $client ) {
						?>
							<option <?php if( $current[ 'client_id' ] == $client->client_id ) { echo 'selected'; } ?> value="<?php echo $client->client_id; ?>"><?php echo $client->client_name; ?></option> 
						<?php	
						}
						?>
						</select><br />
						<a href="<?php $this->friendly_page_link( 'clients' ); ?>" id="add_client_link">Add Client</a><br />
						The client with which you wish to associate this project.
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="project_description">Description</label>
					<td>
						<textarea name="project_description" id="project_description" style="width:97%;" cols="50" rows="7"><?php echo $current[ 'project_description']; ?></textarea><br />
						Enter a longer description of the project, such as the scope and any specifics that you know.
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		if( $current !== FALSE ) {
			?>
			<input type="hidden" name="project_id" id="project_id" value="<?php echo $current[ 'project_id' ]; ?>" />
			<p class="submit">
				<input name="submit_project" id="submit_project" class="button" type="submit" value="Edit Project" />
			</p>
			<?php
		} else {
		?>
		<p class="submit">
			<input name="submit_project" id="submit_project" class="button" type="submit" value="Add Project" />
		</p>
		<?php } ?>
	</form>
	
</div> <!-- End Wrap -->