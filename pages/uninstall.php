<?php

?>
<div class="wrap">
	<h2>Uninstall</h2>
	<div class="narrow">
		<?php
		if( isset( $_POST[ 'uninstall_one' ] ) ) {
			?>
			<p>Are you <strong>absolutely sure</strong> that you want to uninstall the WP-Project plugin?  All
			of your saved options, clients, projects, and tasks will be lost.  <strong>All data will be erased</strong> and you will be
			unable to use WP-Project unless you deactivate and reactivate it.</p>
			<form method="post" action="<?php $this->friendly_page_link( 'top_level' ); ?>">
				<p class="submit">
					<input type="submit" name="uninstall_wp_project_complete" value="Uninstall" />
				</p>
			</form>
			<?php			
		} else {
		?>
		<p>To completely uninstall the WP-Project plugin.  Please click the following button.  All data will be erased.</p>
		<form method="post" action="<?php $this->friendly_page_link( 'uninstall' ); ?>">
				<p class="submit">
					<input type="submit" name="uninstall_one" value="Uninstall" />
				</p>
			</form>
		<?php
		}
		?>
	</div>
</div>