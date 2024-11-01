<?php
/*
Plugin Name: WP-Project
Plugin URI: http://www.plugin-developer.com
Description: WP-Project creates a complete project management system in the WordPress backend.
Author: Nick Ohrn
Version: 2.0.0
Author URI: http://www.plugin-developer.com/
*/

// Avoid name collisions
if( !class_exists( 'WP_Project' ) ) {

	class WP_Project {
		
		var $version = "2.0.0";
		var $is_installed = true;
		
		var $page_slugs;
		
		/**
		 * The following section has the constructor and functions that hook into WordPress.
		 */
		
		/// HOOKS
		
		/**
		 * Default constructor initializes variables and other data needed for the plugin to operate
		 * correctly.
		 *
		 * @return WP_Project A newly constructed instance of the WP_Project object with all data initialized.
		 */
		function WP_Project() {
			global $wpdb;
			
			// Setup the table names
			$wpdb->project_table = $wpdb->prefix . 'project_projects';
			$wpdb->client_table = $wpdb->prefix . 'project_clients';
			$wpdb->task_table = $wpdb->prefix . 'project_tasks';
			$wpdb->project_task_table = $wpdb->prefix . 'project_projects_to_tasks';
			$wpdb->time_table = $wpdb->prefix . 'project_times';
			$wpdb->invoice_table = $wpdb->prefix . 'project_invoices';
			$wpdb->invoice_item_table = $wpdb->prefix . 'project_invoice_items';
			$wpdb->timer_status_table = $wpdb->prefix . 'project_timer_statuses';
			$wpdb->project_participant_table = $wpdb->prefix . 'project_project_participants';
			$wpdb->client_member_table = $wpdb->prefix . 'project_client_members';
			
			// Need to put this here in the creation of the object, but after the naming of all the
			// tables.
			if( isset( $_POST[ 'uninstall_wp_project_complete' ] ) ) {
				$this->uninstall();
			}

			$version = get_option( 'WP-Project Version' );
						
			if( FALSE === $version ) {
				$this->is_installed = false;
				
			}
			
			// Setup the page slugs array
			$this->page_slugs = array();
			
		}
		
		/**
		 * Check to see if tables for the WP-Project plugin are installed and that the plugin is the current version.
		 * If those two things are true, then leave the data alone.  Otherwise, upgrade or install the necessary
		 * tables.
		 */
		function on_activate() {
			
			$current_version = get_option( 'WP-Project Version' );
			
			// Install the various tables if this is a new installation
			if( FALSE === $current_version ) {
				$this->install();
				
			} else { // Upgrade
				$this->upgrade( $current_version );
				
			}
			
		}
		
		/**
		 * This function will not make any changes to data that exists in the database.  That is reserved for the 
		 * uninstall_data function.  For now, this is just a placeholder in case some action becomes necessary
		 * on deactivation.
		 */
		function on_deactivate() {
			// We're not really doing anything on a deactivation, because everything is being uninstalled
			// through a separate mechanism to ensure none of the good data gets erased.
			
		}
		
		/**
		 * Adds all additional pages necessary for the correct administration of WP-Project, as well as enqueueing any
		 * JavaScript files necessary for those files.
		 */
		function on_admin_menu() {
			
			$this->page_slugs[ 'top_level' ] = add_menu_page( 'WP-Project', 'WP-Project', 8, 'wp-project', array( &$this, 'top_level_page' ) );
			
			if( $this->is_installed ) {
				$this->page_slugs[ 'projects' ]  = add_submenu_page( 'wp-project', 'Projects', 'Projects', 8, 'wp-project/projects', array( &$this, 'project_page' ) );
				$this->page_slugs[ 'clients' ]  = add_submenu_page( 'wp-project', 'Clients', 'Clients', 8, 'wp-project/clients', array( &$this, 'client_page' ) );
				$this->page_slugs[ 'tasks' ]  = add_submenu_page( 'wp-project', 'Tasks', 'Tasks', 8, 'wp-project/tasks', array( &$this, 'task_page' ) );
				$this->page_slugs[ 'uninstall' ] = add_submenu_page( 'wp-project', 'Uninstall', 'Uninstall', 8, 'wp-project/uninstall', array( &$this, 'uninstall_page' ) );
				
			}
			
			$this->page_slugs[ 'about' ]  = add_submenu_page( 'wp-project', 'About', 'About', 8, 'wp-project/about', array( &$this, 'about_page' ) );
			$this->page_slugs[ 'donate' ]  = add_submenu_page( 'wp-project', 'Donate', 'Donate', 8, 'wp-project/donate', array( &$this, 'donate_page' ) );
		}
		
		/**
		 * Selectively prints information to the head section of the administrative HTML section.
		 */
		function on_admin_head() {
			// If we're on a WP-Project page, we need to add the appropriate CSS files
			if( strpos( $_SERVER['REQUEST_URI'], 'wp-project' ) ) {
				?>
				<link rel="stylesheet" href="<?php bloginfo( 'siteurl' ); ?>/wp-admin/css/dashboard.css" type="text/css" />
				<link rel="stylesheet" href="<?php bloginfo( 'siteurl' ); ?>/wp-content/plugins/WP-Project/css/wp-project.css" type="text/css" />
				<?php
				// If we're on the tasks page, we need to instantiate the current_timer variable for JavaScript
				if( strpos( $_SERVER[ 'REQUEST_URI' ], 'tasks' ) ) {
				?>
				<script type="text/javascript">
					var old_id = <?php echo $this->options[ 'current_timer' ]; ?>
				</script>
				<?php
				
				}
				
			}
			
		}
		
		/**
		 * Runs to ensure that the user is removed as a project participant and client member in all cases.
		 *
		 * @param int $user_id
		 */
		function on_delete_user( $user_id ) {
			WP_Project_Project::remove_user_from_all_projects( $user_id );
			WP_Project_Client::remove_user_from_all_clients( $user_id );
		}
		
		/**
		 * Toggles the currently active timer, and saves whatever time was 
		 *
		 */
		function on_timer_toggle() {
			$timer_on = WP_Project_Time::toggle( $_POST[ 'timer_id' ], $_POST[ 'current_user' ] );
			
			exit;
		}
		
		/**
		 * The following functions are all utility functions for the plugin.
		 */
		
		/// UTILITY
		
		/**
		 * Installs the plugin for the first time by creating all tables and storing all the options
		 * that need to be stored.
		 */
		function install() {
			
			global $wpdb;
			
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			
			// Create the table to hold information about projects
			$project_table_create = "CREATE TABLE $wpdb->project_table (
									id BIGINT(20) NOT NULL AUTO_INCREMENT,
									client_id BIGINT(20) NOT NULL,
									date_created_gmt DATETIME NOT NULL,
									date_modified_gmt DATETIME NOT NULL,
									project_title TEXT NOT NULL,
									project_slug TEXT NOT NULL,
									project_description TEXT NOT NULL,
									project_cost FLOAT NOT NULL,
									bill_by_project BOOL NOT NULL DEFAULT FALSE,
									percent_billed INT NOT NULL DEFAULT 0,
									PRIMARY KEY (id))";

			$client_table_create = "CREATE TABLE $wpdb->client_table (
									id BIGINT(20) NOT NULL AUTO_INCREMENT,
									date_created_gmt DATETIME NOT NULL,
									date_modified_gmt DATETIME NOT NULL,
									client_name TEXT NOT NULL,
									client_slug TEXT NOT NULL,
									client_description TEXT NOT NULL,
									PRIMARY KEY (id))";
						
			$task_table_create = "CREATE TABLE $wpdb->task_table (
									id BIGINT(20) NOT NULL AUTO_INCREMENT,
									date_created_gmt DATETIME NOT NULL,
									date_modified_gmt DATETIME NOT NULL,
									task_name TEXT NOT NULL,
									task_description TEXT NOT NULL,
									is_billable BOOL NOT NULL DEFAULT FALSE,
									is_default BOOL NOT NULL DEFAULT FALSE,
									hourly_rate FLOAT NOT NULL DEFAULT 0,
									PRIMARY KEY (id))";

			$project_task_table_create = "CREATE TABLE $wpdb->project_task_table (
											id BIGINT(20) NOT NULL AUTO_INCREMENT,
											project_id BIGINT(20) NOT NULL,
											task_id BIGINT(20) NOT NULL,
											number_hours_billed FLOAT NOT NULL DEFAULT 0,
											PRIMARY KEY (id))";
									
			$time_table_create = "CREATE TABLE $wpdb->time_table (
									id BIGINT(20) NOT NULL AUTO_INCREMENT,
									user_id BIGINT(20) NOT NULL,
									project_task_id BIGINT(20) NOT NULL,
									date_created_gmt DATETIME NOT NULL,
									date_modified_gmt DATETIME NOT NULL,
									time_description TEXT NOT NULL,
									number_seconds BIGINT(32) NOT NULL DEFAULT 0,
									PRIMARY KEY (id))";
			
			$invoice_table_create = "CREATE TABLE $wpdb->invoice_table (
										id BIGINT(20) NOT NULL AUTO_INCREMENT,
										project_id BIGINT(20) NOT NULL,
										date_created_gmt DATETIME NOT NULL,
										date_modified_gmt DATETIME NOT NULL,
										PRIMARY KEY(id))";
			
			$invoice_item_table_create = "CREATE TABLE $wpdb->invoice_item_table (
											id BIGINT(20) NOT NULL AUTO_INCREMENT,
											invoice_id BIGINT(20) NOT NULL,
											item_cost FLOAT NOT NULL,
											item_name TEXT NOT NULL,
											item_description TEXT NOT NULL,
											is_service BOOL NOT NULL DEFAULT FALSE,
											number_hours FLOAT NOT NULL DEFAULT 0,
											PRIMARY KEY(id))";
										
			$timer_status_table_create = "CREATE TABLE $wpdb->timer_status_table (
											user_id BIGINT(20) NOT NULL,
											time_id BIGINT(20) NOT NULL)";
			
			$project_participant_table_create = "CREATE TABLE $wpdb->project_participant_table (
													project_id BIGINT(20) NOT NULL,
													user_id BIGINT(20) NOT NULL)";
													
			$client_member_table_create = "CREATE TABLE $wpdb->client_member_table (
											client_id BIGINT(20) NOT NULL,
											user_id BIGINT(20) NOT NULL)";
											
			maybe_create_table( $wpdb->project_table, $project_table_create );
			maybe_create_table( $wpdb->client_table, $client_table_create );
			maybe_create_table( $wpdb->task_table, $task_table_create );
			maybe_create_table( $wpdb->project_task_table, $project_task_table_create );
			maybe_create_table( $wpdb->time_table, $time_table_create );
			maybe_create_table( $wpdb->invoice_table, $invoice_table_create );
			maybe_create_table( $wpdb->invoice_item_table, $invoice_item_table_create );
			maybe_create_table( $wpdb->timer_status_table, $timer_status_table_create );
			maybe_create_table( $wpdb->project_participant_table, $project_participant_table_create );
			maybe_create_table( $wpdb->client_member_table, $client_member_table_create );
			
			
			// Add the two option fields
			add_option( 'WP-Project Version', $this->version );
		}
		
		/**
		 * Completely remove all data and database tables concerned with the WP-Project plugin.  This function should be
		 * called only after the user is warned several times of what will happen if they proceed with this action.
		 * All data that they have entered will be erased permanently and will be unretrievable.
		 */
		function uninstall() {
			global $wpdb;
			
			$this->maybe_drop_table( $wpdb->project_table );
			$this->maybe_drop_table( $wpdb->client_table );
			$this->maybe_drop_table( $wpdb->task_table );
			$this->maybe_drop_table( $wpdb->project_task_table );
			$this->maybe_drop_table( $wpdb->time_table );
			$this->maybe_drop_table( $wpdb->invoice_table );
			$this->maybe_drop_table( $wpdb->invoice_item_table );
			$this->maybe_drop_table( $wpdb->timer_status_table );
			$this->maybe_drop_table( $wpdb->project_participant_table );
			$this->maybe_drop_table( $wpdb->client_member_table );
			
			// Delete the options
			delete_option( 'WP-Project Version' );
			delete_option( 'WP-Project Options' );
		}
		
		/**
		 * Drops a table if it exists.
		 */
		function maybe_drop_table( $table_name ) {
			global $wpdb;
			if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
				$wpdb->query( "DROP TABLE {$table_name}" );
			}
		}
		
		/**
		 * Uninstalls version one of the plugin.  Only called when updating from version 1.x.x
		 *
		 */
		function uninstall_version_one() {
			global $wpdb;
			
			$tasks = $wpdb->get_results( "SELECT * FROM {$wpdb->task_table}", OBJECT );
			$clients = $wpdb->get_results( "SELECT * FROM {$wpdb->client_table}", OBJECT );
			$projects = $wpdb->get_results( "SELECT * FROM {$wpdb->project_table}", OBJECT );
			
			$this->maybe_drop_table( $wpdb->project_table );
			$this->maybe_drop_table( $wpdb->client_table );
			$this->maybe_drop_table( $wpdb->task_table );
						
			// Delete the options
			delete_option( 'WP-Project Version' );
			delete_option( 'WP-Project Options' );
			
			return array( 'projects' => $projects, 'clients' => $clients, 'tasks' => $tasks );
		}
		
		/**
		 * Restores the data from version one of the plugin.
		 *
		 * @param array $data
		 */
		function restore_version_one_data( $data ) {
			
		}
		
		/**
		 * Upgrades the plugin from an old version by conditionally updating tables and doing some other sweet stuff.
		 *
		 * @param string $old_version The version number of the previous version that was installed.
		 */
		function upgrade( $old_version ) {
			if( $old_version[ 0 ] == '0' ) {
				$this->uninstall_version_one();
				$this->install();
			} else if( $old_version[ 0 ] == '1' ) {
				$data = $this->uninstall_version_one();
				$this->install();
				$this->restore_version_one_data( $data );
			} else if( $old_version[ 0 ] == '2' ) {
				 // Do nothing, already at the upgraded version
			}
			
			update_option( 'WP-Project Version', $this->version );
		}
		
		/** 
		 * Prepare a backup of all data contained in the WP-Project tables.  A full SQL dump and a CSV dump are available. 
		 */
		function backup_data( $backup_type = 'cvs' ) {
			switch( $backup_type ) {
				case 'sql':
					
					break;
					
				// Perform a comma separated value data 
				case 'csv':
				default:
					
					break;
			}
		}
		
		// CLIENT
		
		/**
		 * Returns the number of clients currently in the system.
		 *
		 * @return int the number of clients currently in the system.
		 */
		function client_count() {
			return count( $this->get_clients() );
		}
		
		/**
		 * Retrieves an optionally paginated list of clients.
		 * 
		 * @return array An array of clients in the system.
		 */
		function get_clients( $page = null ) {
			global $wpdb;
			
			$query = "SELECT * FROM $wpdb->client_table WHERE client_id >= 0";
			
			return $wpdb->get_results( $query, OBJECT );
		}
		
		/**
		 * Returns a single client, as identified by its id.
		 *
		 * @param int $id the id of the client to retrieve.
		 * @return array the client to be returned.
		 */
		function get_client( $id ) {
			global $wpdb;
			
			$query = "SELECT * FROM $wpdb->client_table WHERE client_id = " . $wpdb->escape( $id );
			
			return $wpdb->get_row( $query, ARRAY_A );
		}
		
		/**
		 * Validates the values of a client declaration.
		 *
		 * @param array $client_values the values to store for a client.
		 */
		function validate_client( $client_values ) {
			$errors = array();
			
			if( strlen( $client_values[ 'client_name' ] ) <= 0 ) {
				$errors[] = 'Client name must be provided.  Please enter a name.';
			}
			
			if( strlen( $client_values[ 'client_name' ] ) > 200 ) {
				$errors[] = 'Client name is too long.  Please limit lient names to 200 characters.';
			}
			
			if( strlen( $client_values[ 'client_description' ] ) > 2000 ) {
				$errors[] = 'Client description is too long. Please limit client descriptions to 2000 characters.';
			}
			
			if( strlen( $client_values[ 'client_email' ] ) > 200 ) {
				$errors[] = 'Client email is too long. Please limit client email address to 200 characters.';
			}
			
			if( !is_email( $client_values[ 'client_email' ] ) ) {
				$errors[] = 'Client email is not valid.  Please enter a valid email address.';
			}
			
			if( strlen( $client_values[ 'client_site' ] ) > 200 ) {
				$errors[] = 'Client site address is too long.  Please limit client site addresses to 200 characters.';
			}
			
			return $errors;
		}
		
		/**
		 * Adds a new client to the system.
		 *
		 * @param string $name the name of the client.
		 * @param string $email the email of the client.
		 * @param string $site the client's web site.
		 * @param string $description the description of the client.
		 */
		function add_client( $name, $email, $site, $description ) {
			global $wpdb;
			
			$name = $wpdb->escape( $name );
			$email = $wpdb->escape( $email );
			$site = $wpdb->escape( $site );
			$description = $wpdb->escape( $description );
			
			$query = "INSERT INTO $wpdb->client_table (client_name, client_email, client_site, client_description) VALUES( '$name', '$email', '$site', '$description' )";
			
			return $wpdb->query( $query );
		}
		
		/**
		 * Edits a currently existing client.
		 *
		 * @param int $id the unique identifier for the client being edited.
		 * @param string $name the name of the client.
		 * @param string $email the email of the client.
		 * @param string $site the client's web site.
		 * @param string $description the description of the client.
		 */
		function edit_client( $id, $name, $email, $site, $description ) {
			global $wpdb;

			$id = $wpdb->escape( $id );
			$name = $wpdb->escape( $name );
			$email = $wpdb->escape( $email );
			$site = $wpdb->escape( $site );
			$description = $wpdb->escape( $description );
			
			$query = "UPDATE $wpdb->client_table SET client_name = '$name', client_email = '$email', client_site = '$site', client_description = '$description' WHERE client_id = $id";
			
			$wpdb->query( $query );
		}
		
		/**
		 * Determines whether or not a client is currently being edited.
		 *
		 * @return array the current client being edited.
		 */
		function is_editing_client( ) {
			if( $_GET[ 'action' ] == 'edit' && ( $client = $this->get_client( $_GET[ 'id' ] ) ) !== FALSE ) {
				return $client;
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Deletes a single client entry and any associated projects.
		 *
		 * @param int $id the unique identifier for the client.
		 */
		function delete_client( $id ) {
			global $wpdb;
			$wpdb->query( "DELETE FROM $wpdb->client_table WHERE client_id = " . $wpdb->escape ( $id ) );
			$projects = $wpdb->get_results( "SELECT project_id FROM $wpdb->project_table P, $wpdb->client_table C WHERE C.client_id = P.client_id", OBJECT );
			foreach( $projects as $project ) {
				$this->delete_project( $project->project_id );
			}
		}
		
		/**
		 * Deletes a number of clients from the system.
		 * 
		 * @param array the ids of clients that should be deleted.
		 */
		function delete_clients( $client_ids ) {
			if( is_array( $client_ids ) ) {
				foreach( $client_ids as $id ) {
					$this->delete_client( $id );;
				}
			}
		}
		
		// TASK
		
		/**
		 * Returns the number of tasks currently in the system, optionally for a specific project.
		 *
		 * @param int $project_id the id of the project to determine the number of tasks for.
		 */
		function task_count( $project_id = null ) {
			return count( $this->get_tasks( $project_id ) );
		}
		
		/**
		 * Retrieves an optionally paginated list of tasks for a particular project.
		 * 
		 * @return array An array of tasks.
		 */
		function get_tasks( $project_id = null, $page = null ) {
			global $wpdb;
			
			$query = "SELECT task_id, project_title, T.project_id, task_name, task_description, task_priority, task_time, task_status FROM $wpdb->task_table T, $wpdb->project_table P WHERE P.project_id = T.project_id";
			
			if( $project_id !== null ) {
				$query .= " AND T.project_id = " . $wpdb->escape( $project_id );
			}
			
			$query .= " ORDER BY task_priority DESC";
			
			return $wpdb->get_results( $query, OBJECT );
		}
		
		/**
		 * Returns a task object for the passed unique identifier
		 *
		 * @param int $id the unique identifier for the task.
		 * @return mixed FALSE if no task exists for the specified id and the task object otherwise.
		 */
		function get_task( $id ) {
			global $wpdb;
			
			$query = "SELECT task_id, project_title, T.project_id, task_name, task_description, task_priority, task_time, task_status FROM $wpdb->task_table T, $wpdb->project_table P WHERE P.project_id = T.project_id AND task_id = " . $wpdb->escape( $id );
			
			return $wpdb->get_row( $query, ARRAY_A );
		}
		
		/**
		 * Validates task values and returns an array of errors messages based on validation.
		 *
		 * @param array $task_values an array of task values to use to create or edit a task.
		 * @return array an array filled with any error messages generated.
		 */
		function validate_task( $task_values ) {
			$errors = array();
			
			if( !is_numeric( $task_values[ 'project_id' ] ) ) {
				$errors[] = 'Invalid project id.';
			}
			
			if( strlen( $task_values[ 'task_name' ] ) <= 0 ) {
				$errors[] = 'Task name must be provided.  Please enter a name.';
			}
			
			if( strlen( $task_values[ 'task_name' ] ) > 200 ) {
				$errors[] = 'Task name is too long.  Please limit task name to 200 characters.';
			}
			
			if( strlen( $task_values[ 'task_description' ] ) > 2000 ) {
				$errors[] = 'Task description is too long.  Please limit task description to 2000 characters.';
			}
			
			if( !is_numeric( $task_values[ 'task_priority' ] ) ) {
				$errors[] = 'Task priority must be be numeric.';
			}
			
			if( !is_numeric( $task_values[ 'task_time' ] ) ) {
				$errors[] = 'Task time must be numeric.';
			}
			
			if( strlen( $task_values[ 'task_status' ] ) > 100 ) {
				$errors[] = 'Task status is too long.  Please limit task status to 100 characters.';
			}
			
			return $errors;
		}
		
		/**
		 * Adds a task to the system.
		 *
		 * @param int $project_id the unique identifier for the project this task is concerned with.
		 * @param string $name the name of the task.
		 * @param string $description a brief description of the task.
		 * @param int $priority the numeric priority of the task.
		 * @param float $time the number of hours this task has taken so far.
		 * @param string $status the status of the task (complete, incomplete, needs testing)
		 */
		function add_task( $project_id, $name, $description, $priority, $time, $status ) {
			global $wpdb;
			
			$project_id = $wpdb->escape( $project_id );
			$name = $wpdb->escape( $name );
			$description = $wpdb->escape( $description );
			$priority = $wpdb->escape( $priority );
			$time = $wpdb->escape( $time );
			$status = $wpdb->escape( $status );

			$query = "INSERT INTO $wpdb->task_table (project_id, task_name, task_description, task_priority, task_time, task_status) VALUES ($project_id, '$name', '$description', $priority, $time, '$status')";
			
			$wpdb->query( $query );
		}
		
		/**
		 * Edits an existing task.
		 * 
		 * @param int $id the unique identifier for the task being edited.
		 * @param int $project_id the unique identifier for the project this task is concerned with.
		 * @param string $name the name of the task.
		 * @param string $description a brief description of the task.
		 * @param int $priority the numeric priority of the task.
		 * @param float $time the number of hours this task has taken so far.
		 * @param string $status the status of the task (complete, incomplete, needs testing)
		 */
		function edit_task($id, $project_id, $name, $description, $priority, $time, $status ) {
			global $wpdb;
			
			$id = $wpdb->escape( $id );
			$project_id = $wpdb->escape( $project_id );
			$name = $wpdb->escape( $name );
			$description = $wpdb->escape( $description );
			$priority = $wpdb->escape( $priority );
			$time = $wpdb->escape( $time );
			$status = $wpdb->escape( $status );

			$query = "UPDATE $wpdb->task_table SET project_id = $project_id, task_name = '$name', task_description = '$description', task_priority = $priority, task_time = $time, task_status = '$status' WHERE task_id = $id";
			
			$wpdb->query( $query );
		}
		
		/**
		 * Adds the specified number of seconds to the task identified by id.
		 *
		 * @param int $id the unique identifier for the task.
		 * @param int $seconds the number of seconds to add to the total time.
		 */
		function add_seconds_to_task( $id, $seconds ) {
			global $wpdb;
			
			$wpdb->query( "UPDATE $wpdb->task_table SET task_time = task_time + " . $this->seconds_to_hours( $seconds ) . " WHERE task_id = " . $wpdb->escape( $id ) );
		}
		
		/**
		 * Checks the request variables to decide whether a valid client is being edited.
		 *
		 * @return mixed FALSE if a task is not being edited or the task object if one is being
		 * edited.
		 */
		function is_editing_task( ) {
			if( $_GET[ 'action' ] == 'edit' && ( $task = $this->get_task( $_GET[ 'id' ] ) ) !== FALSE ) {
				return $task;
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Deletes a single task entry from the system.
		 *
		 * @param int $id the unique identifer for the task to delete.
		 */
		function delete_task( $id ) {
			global $wpdb;
			$wpdb->query( "DELETE FROM $wpdb->task_table WHERE task_id = " . $wpdb->escape( $id ) );
		}
		
		/**
		 * Removes the tasks with specified Ids from the system.
		 *
		 * @param array $task_ids an array of task Ids to delete.
		 */
		function delete_tasks( $task_ids ) {
			if( is_array( $task_ids ) ) {
				foreach( $task_ids as $id ) {
					$this->delete_task( $id );
				}
			}
		}
	
		/**
		 * Returns an array of priority options.
		 */
		function priority_options() {
			return array( 5 => 'Critical', 4 => 'High', 3 => 'Normal', 2 => 'Low', 1 => 'Trivial' );
		}
		
		/**
		 * Converts seconds to hours.
		 *
		 * @param int $seconds
		 * @return float the amount of hours the seconds is equal to.
		 */
		function seconds_to_hours( $seconds ) {
			return $seconds / 3600;
		}
		
		/**
		 * The following functions are the display functions for the various pages in the WP-Project 
		 * plugin's interface.
		 */
		
		/**
		 * Displays the dashboard for the WP-Project plugin.
		 */
		function top_level_page() { 
			if( $this->is_installed ) {
				include( dirname( __FILE__ ) . '/pages/dashboard.php' );
			} else {
				echo '<div class="wrap"><p>WP-Project is uninstalled.  Please deactivate the plugin.</p></div>';
			}
		}
	
		/**
		 * Displays the project page for the WP-Project plugin.
		 */
		function project_page() { 
			include( dirname( __FILE__ ) . '/pages/projects.php' );
		}
	
		/**
		 * Displays the client page for the WP-Project plugin.
		 */
		function client_page() { 
			include( dirname( __FILE__ ) . '/pages/clients.php' );
		}
	
		/**
		 * Displays the task page for the WP-Project plugin.
		 */
		function task_page() { 
			include( dirname( __FILE__ ) . '/pages/tasks.php' );
		}
	
		/**
		 * Displays the billing page for the WP-Project plugin.
		 */
		function billing_page() { 
			include( dirname( __FILE__ ) . '/pages/billing.php' );
		}
	
		/**
		 * Displays the option page for the WP-Project plugin.
		 */
		function option_page() { 
			include( dirname( __FILE__ ) . '/pages/options.php' );
		}
				
		/**
		 * Displays the uninstall page for the WP-Project plugin.
		 */
		function uninstall_page() {
			include( dirname( __FILE__ ) . '/pages/uninstall.php' );
		}
		
		/**
		 * Displays the about page for the WP-Project plugin.
		 */
		function about_page() { 
			include( dirname( __FILE__ ) . '/pages/about.php' );
		}
		
		/**
		 * Displays the help page for the WP-Project plugin.
		 */
		function donate_page() { 
			include( dirname( __FILE__ ) . '/pages/donate.php' );
		}
		
		/**
		 * The following section contains display helpers.
		 */
		
		/**
		 * Truncates a string if it is longer than 125 characters.  Adds 
		 * ellipses if the string is longer than it should be.
		 *
		 * @param string $string the string to truncate.
		 * @return string the truncated string.
		 */
		function truncate( $string, $length = 125 ) {
			return ( strlen( $string ) > $length ? substr( $string, 0, $length ) . '...' : $string );
		}
		
		/**
		 * Prints paginated rows of projects for use in the project table.
		 *
		 * @param int $page the page to use for pagination.
		 */
		function project_rows( $page = null ) {
			$projects = $this->get_projects( $page );
			
			foreach( $projects as $project ) {
				$class = ( $class == 'alternate' ? '' : 'alternate' );
			?>
			<tr class="<?php echo $class; ?>" id="project_row-<?php echo $project->project_id; ?>">
				<th class="check-column" scope="row"><input id="project_cb-<?php echo $project->project_id; ?>" name="project_cb[<?php $project->project_id; ?>]" type="checkbox" value="<?php echo $project->project_id; ?>" /></th>
				<td><a href="<?php $this->friendly_page_link( 'projects' ); ?>&amp;action=edit&amp;id=<?php echo $project->project_id; ?>"><?php echo $project->project_title; ?></a></td>
				<td><a href="<?php $this->friendly_page_link( 'clients' ); ?>&amp;action=edit&amp;id=<?php echo $project->client_id; ?>"><?php echo $project->client_name; ?></a></td>
				<td><?php echo $this->truncate( $project->project_description ); ?></td>
			</tr>
			<?php	
			}
		}
		
		/**
		 * Prints paginated rows of clients for use in the client management table.
		 *
		 * @param int $page the page to print.
		 */
		function client_rows( $page = null ) {
			$clients = $this->get_clients( $page );
			
			foreach( $clients as $client ) {
				$class = ( $class == 'alternate' ? '' : 'alternate' );
			?>
			<tr class="<?php echo $class; ?>" id="client_ro-<?php echo $client->client_id; ?>">
				<th class="check-column" scope="row"><input id="client_cb-<?php echo $client->client_id; ?>" name="client_cb[<?php echo $client->client_id; ?>]" type="checkbox" value="<?php echo $client->client_id; ?>" /></th>
				<td><a href="<?php $this->friendly_page_link( 'clients' ); ?>&amp;action=edit&amp;id=<?php echo $client->client_id; ?>"><?php echo $client->client_name; ?></a></td>
				<td><a href="mailto:<?php echo $client->client_email; ?>"><?php echo $client->client_email; ?></a></td>
				<td><a href="<?php echo $client->client_site; ?>"><?php echo $this->truncate( $client->client_site, 55 ); ?></a></td>
				<td><?php echo $this->truncate( $client->client_description ); ?></td>
			</tr> 
			
			<?php	
			}
		}
		
		/**
		 * Prints paginated rows of tasks for use in the task management table.
		 *
		 * @param int $page the page to print.
		 */
		function task_rows( $project_id = null, $page = null ) {
			$tasks = $this->get_tasks( $project_id, $page );
			$priorities = $this->priority_options();

			$current_task_running = $this->options[ 'current_timer' ];
			
			foreach( $tasks as $task ) {
				$class = ( $class == 'alternate' ? '' : 'alternate' );
				?>
				<tr class="<?php echo $class; ?>" id="task_row-<?php echo $task->task_id; ?>">
					<th class="check-column" scope="row"><input id="task_cb-<?php echo $task->task_id; ?>" name="task_cb[<?php echo $task->task_id; ?>]" type="checkbox" value="<?php echo $task->task_id; ?>" /></th>
					<td><a href="<?php $this->friendly_page_link( 'tasks' ); ?>&amp;action=edit&amp;id=<?php echo $task->task_id; ?>"><?php echo $task->task_name; ?></a></td>
					<td><?php echo $this->truncate( $task->task_description ); ?></td>
					<td><a href="<?php $this->friendly_page_link( 'projects' ); ?>&amp;action=edit&amp;id=<?php echo $task->project_id; ?>"><?php echo $task->project_title; ?></a></td>
					<td><?php echo $priorities[ $task->task_priority ]; ?></td>
					<td><a id="timer_toggle_<?php echo $task->task_id; ?>" class="timer<?php if( $current_task_running == $task->task_id ) { echo " timer_on"; } ?>" title="<?php echo bloginfo( 'siteurl' ); ?>" rel="<?php echo $task->task_id; ?>" href="<?php $this->friendly_page_link( 'tasks' ); ?>&amp;action=start_timer&amp;id=<?php echo $task->task_id; ?>"></a><span id="timer_time_<?php echo $task->task_id; ?>"><?php printf( "%.2f", $task->task_time + ( $this->options[ 'current_timer' ] == $task->task_id ? ( $this->seconds_to_hours( time() - $this->options[ 'timer_started' ] ) ) : 0 ) ); ?></span> hr(s)</td>
					<td><?php echo $task->task_status; ?></td>
				</tr>
				<?php
			}
		}
		
		/**
		 * Returns or displays a friendly page slug.
		 *
		 * @param string $slug_id The string identifying the page to be referenced.
		 * @param bool $display Whether to return or display the value.
		 */
		function friendly_page_slug( $slug_id, $display = true ) {
			if( isset( $this->page_slugs[ $slug_id ] ) ) {
				$array = explode( '_page_', $this->page_slugs[ $slug_id ] );
				if( $display ) {
					echo $array[ 1 ];
				} else {
					return $array[ 1 ];
				}
			} else if( $slug_id == 'top_level' ) {
				if( $display ) {
					echo 'wp-project';
				} else {
					return 'wp-project';
				}
			}
		}
		
		/**
		 * Returns or display a friendly link between pages in WP-Project. 
		 *
		 * @param string $slug_id the id for the page to be displayed.
		 * @param bool $display Whether to display or return the value.
		 */
		function friendly_page_link( $slug_id, $display = true ) {
			$page_slug = $this->friendly_page_slug( $slug_id, false );
			
			$value = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . $page_slug;
			if( $display ) {
				echo $value;
			} else {
				return $value;
			}
		}
			
	}

}
// Ensure the class exists before instantiating an object of this type
if( class_exists( 'WP_Project' ) ) {
	
	$wp_project = new WP_Project();
	
	// Activation and Deactivation
	register_activation_hook( __FILE__, array( &$wp_project, 'on_activate' ) );
	register_deactivation_hook( __FILE__, array( &$wp_project, 'on_deactivate' ) );
	
	// Actions
	add_action( 'admin_menu', array( &$wp_project, 'on_admin_menu' ) );
	add_action( 'admin_head', array( &$wp_project, 'on_admin_head' ) );
	add_action( 'delete_user', array( &$wp_project, 'on_delete_user' ) );
	add_action( 'wp_ajax_timer_toggle', array( &$wp_project, 'on_timer_toggle' ) );
	
	// Filters
	
}



?>