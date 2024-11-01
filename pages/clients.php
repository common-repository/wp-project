<?php
if( isset( $_POST[ 'delete_clients' ] ) ) {
	$this->delete_clients( $_POST[ 'client_cb' ] );
}

if( isset( $_POST[ 'submit_client' ] ) ) {
	
	$errors = $this->validate_client( $_POST );
	
	if( empty( $errors ) ) {
	
		if( isset( $_POST[ 'client_id' ] ) ) {
			// Edit existing
			$results = $this->edit_client( $_POST[ 'client_id' ], $_POST[ 'client_name' ], $_POST[ 'client_email' ], $_POST[ 'client_site' ], $_POST[ 'client_description' ] );
			
			if( $results === FALSE ) {
				?>
				<div id="message" class="error">
					<p>There were unexpected errors during the update.  Your changes may not have been saved.</p>
				</div>	
				<?php				
			} else { 
				?>
				<div id="message" class="updated fade">
					<p>Your client "<?php echo stripslashes( $_POST[ 'client_name' ] ); ?>" has been updated.</p>
				</div>	
				<?php
			}
			
		} else {
			// Add new
			$results = $this->add_client( $_POST[ 'client_name' ], $_POST[ 'client_email' ], $_POST[ 'client_site' ], $_POST[ 'client_description' ] );
			
			if( $results === FALSE ) {
				?>
				<div id="message" class="error">
					<p>There were unexpected errors during the add.  Your changes may not have been saved.</p>
				</div>	
				<?php	
			} else { 
				?>
				<div id="message" class="updated fade">
					<p>Your client "<?php echo stripslashes( $_POST[ 'client_name' ] ); ?>" has been added.</p>
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
		
		$current[ 'client_id' ] = $_POST[ 'client_id' ];
		$current[ 'client_name' ] = $_POST[ 'client_name' ];
		$current[ 'client_site' ] = $_POST[ 'client_site' ];
		$current[ 'client_email' ] = $_POST[ 'client_email' ];
		$current[ 'client_description' ] = $_POST[ 'client_description' ];
	
	}
}

$current = $this->is_editing_client();
if( $current !== FALSE ) {
	?>
<div class="wrap">
	<h2>Edit Client</h2>

	<?php
} else {
?>
<div class="wrap">
	<form name="client-manage" id="client-manage" method="post" action="<?php $this->friendly_page_link( 'clients' ); ?>">
		<h2>Manage Clients (<a href="#addclient">add new</a>)</h2>
		<div class="tablenav">
			<div class="alignleft">
				<input name="delete_clients" id="delete_clients" class="button-secondary delete" type="submit" value="Delete" />
			</div>
			<br class="clear" />
		</div>
		<br class="clear" />
		
		<table class="widefat"> <!-- Start Manage Table -->
			<thead>
				<tr>
					<th class="check-column" scope="col"><input id="selectall" name="selectall" type="checkbox" /></th>
					<th scope="col">Name</th>
					<th scope="col">Email</th>
					<th scope="col">Site</th>
					<th scope="col">Description</th>
				</tr>
			</thead>
			<tbody>
				<?php $this->client_rows(); ?>
			</tbody>
		</table> <!-- End Manage Table -->
		
	</form> <!-- End the manage form -->
	<div class="tablenav">
		<br class="clear" />
	</div>
	<br class="clear" />
</div>

<div class="wrap">
	<h2>Add Client</h2>
<?php
}
?>
	<form name="addclient" id="addclient" method="post" action="<?php $this->friendly_page_link( 'clients' ); echo ( $current !== FALSE ? '&action=edit&id=' . $current[ 'client_id' ] : '' ); ?>">
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="client_name">Name</label></th>
					<td>
						<input id="client_name" name="client_name" type="text" size="30" value="<?php echo $current[ 'client_name']; ?>" /><br />
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="client_email">Email</label></th>
					<td>
						<input id="client_email" name="client_email" type="text" size="30" value="<?php echo $current[ 'client_email' ]; ?>" /><br />
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="client_site">Web Site</label></th>
					<td>
						<input id="client_site" name="client_site" type="text" size="30" value="<?php echo $current[ 'client_site' ]; ?>" /><br />
						Ensure that you use the full address (i.e. http://example.com).  Don't leave off the <code>http://</code>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="client_description">Description</label>
					<td>
						<textarea name="client_description" id="client_description" style="width:97%;" cols="50" rows="7"><?php echo $current[ 'client_description']; ?></textarea><br />
						Enter a longer description for this client, such as the type of work that they usually get done.
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		if( $current !== FALSE ) {
			?>
			<input type="hidden" name="client_id" id="client_id" value="<?php echo $current[ 'client_id' ]; ?>" />
			<p class="submit">
				<input name="submit_client" id="submit_client" class="button" type="submit" value="Edit Client" />
			</p>
			<?php
		} else {
		?>
		<p class="submit">
			<input name="submit_client" id="submit_client" class="button" type="submit" value="Add Client" />
		</p>
		<?php } ?>
	</form>

</div>