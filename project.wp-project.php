<?php

/**
 * This class represents a project in the WP-Project system.
 */
class WP_Project_Project {
	
	/**
	 * The unique identifier for this project
	 *
	 * @var int
	 */
	var $_id = null;
	
	/**
	 * A boolean flag indicating whether this project bills by the project.
	 *
	 * @var bool
	 */
	var $_bill_by_project = false;
	
	/**
	 * The unique identifier for the client who owns this project.
	 *
	 * @var int
	 */
	var $_client_id = null;
	
	/**
	 * The client information for this particular project.
	 * 
	 * @var WP_Project_Client
	 */
	var $_client = null;
	
	/**
	 * The cost for this project in dollars.
	 *
	 * @var float
	 */
	var $_cost = null;
	
	/**
	 * The date and time at which this project was created.
	 *
	 * @var string
	 */
	var $_created = null;
	
	/**
	 * A brief description of this project.  The description can be from 0 - 2000 characters in length.
	 *
	 * @var string
	 */
	var $_description = null;
	
	/**
	 * The date and time at which this project was last modified.
	 *
	 * @var string
	 */
	var $_modified = null;
	
	/**
	 * The percentage of this project that has been billed formally through an invoice.
	 *
	 * @var float
	 */
	var $_percent_billed = null;
	
	/**
	 * The slug for this particular project.
	 *
	 * @var string
	 */
	var $_slug = null;
	
	/**
	 * An array of task objects that this project is concerned with.
	 *
	 * @var array
	 */
	var $_tasks = null;
	
	/**
	 * A descriptive title for the project.
	 *
	 * @var string
	 */
	var $_title = null;
	
	/**
	 * An array of strings describing errors with the project.
	 *
	 * @var array
	 */
	var $_validation_errors = array();
	
	/**
	 * Populates a WP_Project_Project from an array of values.
	 *
	 * @param array $array
	 */
	function populate_from_array( $array ) {
		if( is_array( $array ) ) {
			$this->_id = $result[ 'id' ];
			$this->_client_id = $result[ 'client_id' ];
			$this->_created = $result[ 'date_created_gmt' ];
			$this->_modified = $result[ 'date_modified_gmt' ];
			$this->_title = $result[ 'project_title' ];
			$this->_slug = $result[ 'project_slug' ];
			$this->_description = $result[ 'project_description' ];
			$this->_cost = $result[ 'project_cost' ];
			$this->_bill_by_project = $result[ 'bill_by_project' ];
			$this->_percent_billed = $result[ 'percent_billed' ];
		}
	}
	
	/**
	 * Saves this project to the database system.
	 * 
	 * @return boolean indicates whether the save was succesful.
	 */
	function save() {
		if( empty( $this->_validation_errors ) ) {
			global $wpdb;
			if( $this->_id !== null ) {
				$query = "UPDATE $wpdb->project_table
							SET
							client_id = {$this->_client_id},
							date_modified_gmt = NOW(),
							project_title = '{$this->_title}',
							project_slug = '($this->_slug}',
							project_description = '{$this->_description}',
							project_cost = {$this->_cost},
							bill_by_project = {$this->_bill_by_project},
							percent_billed = {$this->_percent_billed}
							WHERE
							id = {$this->_id}";
				
			} else {
				$query = "INSERT INTO $wpdb->project_table
							(client_id,
							date_created_gmt,
							date_modified_gmt,
							project_title,
							project_slug,
							project_description,
							project_cost,
							bill_by_project,
							percent_billed)
							VALUES
							({$this->_client_id},
							NOW(),
							NOW(),
							'{$this->_title}',
							'{$this->_slug}',
							'{$this->_description}',
							{$this->_cost},
							{$this->_bill_by_project},
							{$this->_percent_billed})";
				
			}
			
			return $wpdb->query( $query );
		} else {
			return false;
		}
	}
	
	/// SHOULD BE USED STATICALLY

	function delete( $id ) {
		global $wpdb;
		
		// Clean up the argument
		$id = $wpdb->escape( intval( $id ) );

		// TODO delete form all applicable tables
	}
	
	/**
	 * Retrieves a project from the database by ID.  Also retrieves client information 
	 * and all tasks for the particular project.
	 *
	 * @param int $id
	 * @return WP_Project_Project
	 */
	function get( $id ) {
		global $wpdb;
		
		// Clean up the passed argument
		$id = $wpdb->escape( intval( $id ) );
		
		// Retrieve the database row
		$result = $wpdb->get_row( "SELECT * FROM $wpdb->project_table WHERE id = $id", ARRAY_A );
		
		$project = new WP_Project_Project();
		$project->populate_from_array( $result );
		return $project;
	}
	
	/**
	 * Returns an array of all projects in the system.
	 *
	 * @return array
	 */
	function get_all() {
		$results = array();
		
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT * FROM $wpdb->project_table", ARRAY_A );
		foreach( $rows as $row ) {
			$project = new WP_Project_Project();
			$project->populate_from_array( $result );
			$results[] = $project;
		}
		
		return $results;
	}
	
	/**
	 * Returns the number of projects currently in the system.
	 *
	 * @return int
	 */
	function number_projects() {
		global $wpdb;
		
		return $wpdb->get_var( "SELECT COUNT(id) FROM $wpdb->project_table" );
	}
	
	/**
	 * Deletes a user from the participant list for all projects.
	 *
	 * @param int $user_id
	 */
	function remove_user_from_all_projects( $user_id ) {
		global $wpdb;
		
		$user_id = $wpdb->escape( intval( $user_id ) );
		
		$wpdb->query( "DELETE FROM $wpdb->project_participant_table WHERE user_id = $user_id" );
	}
	
	/**
	 * Deletes a user from the participant list for the specified project.
	 *
	 * @param int $user_id
	 * @param int $project_id
	 */
	function remove_user_from_project( $user_id, $project_id ) {
		global $wpdb;
		
		$user_id = $wpdb->escape( intval( $user_id ) );
		$project_id = $wpdb->escape( intval( $project_id ) );
		
		$wpdb->query( "DELETE FROM $wpdb->project_participant_table WHERE user_id = $user_id AND project_id = $project_id" );
	}
	
	/// GETTERS and SETTERS
	
	/**
	 * Returns the id for this project.
	 *
	 * @return int
	 */
	function get_id() {
		return $this->_id;
	}
	
	/**
	 * Validates and sets the id for this project.
	 *
	 * @param int $id
	 */
	function set_id( $id ) {
		if( is_numeric( $id ) ) {
			$this->_id = intval( $id );
		} else {
			$this->_validation_errors[ 'id' ] = __( 'The project id must be numeric.' );
		}
	}
	
	/**
	 * Returns whether or not this project bills by the cost of the project.
	 *
	 * @return bool
	 */
	function get_bill_by_project() {
		return $this->_bill_by_project;
	}
	
	/**
	 * Sets whether ot not this project bills by the project.
	 *
	 * @param bool $flag
	 */
	function set_bill_by_project( $flag ) {
		if( is_bool( $flag ) ) {
			$this->_bill_by_project = $flag;
		} else {
			$this->_validation_errors[ 'bill_by_project' ] = __( 'The bill by project value must be a boolean.' );
		}
	}
	
	/**
	 * Returns the client id for this project.
	 *
	 * @return int
	 */
	function get_client_id() {
		return $this->_id;
	}
	
	/**
	 * Sets the client id for this project.
	 *
	 * @param int $id
	 */
	function set_client_id( $id ) {
		if( is_numeric( $id ) ) {
			$this->_client_id = intval( $id );
		} else {
			$this->_validation_errors[ 'client_id' ] = __( 'The client id must be numeric.' );
		}
	}
		
	/**
	 * Returns a client object representing the client for this project.
	 *
	 * This implements lazy loading, so it will only load the client from
	 * the database the first time this is called.
	 * 
	 * @return WP_Project_Client
	 */
	function get_client() {
		if( $this->_client === null ) {
			$this->_client = WP_Project_Client::get( $this->_client_id );
		}
		
		return $this->_client;
	}
	
	/**
	 * Returns the cost for this project.
	 *
	 * @return float
	 */
	function get_cost() {
		return $this->_cost;
	}
	
	/**
	 * Sets the cost for this project.
	 *
	 * @param float $cost
	 */
	function set_cost( $cost ) {
		if( is_numeric( $cost ) ) {
			$this->_cost = floatval( $cost );
		} else {
			$this->_validation_errors[ 'project_cost' ] = __( 'The project cost must be numeric.' );
		}
	}
	
	/**
	 * Returns the datetime at which this project was created.
	 *
	 * @return string
	 */
	function get_created_date_gmt() {
		return $this->_created;
	}
	
	/**
	 * Returns the description for this project.
	 *
	 * @return string
	 */
	function get_project_description() {
		return $this->_description;
	}
	
	/**
	 * Sets the description for this project.
	 *
	 * @param string $description
	 */
	function set_project_description( $description ) {
		$this->_description = $description;
	}
	
	/**
	 * Returns the datetime at which this project was last modified.
	 *
	 * @return string
	 */
	function get_modified_date_gmt() {
		return $this->_modified;
	}

	/**
	 * Returns the percentage of this project that has been billed.  Only
	 * applicable if the project is being billed by the project.
	 *
	 * @return float
	 */
	function get_percent_billed() {
		return $this->_percent_billed;
	}
	
	/**
	 * Sets the percentage of this project that has been billed.
	 *
	 * @param float $percent
	 */
	function set_percent_billed( $percent ) {
		if( is_numeric( $percent ) ) {
			$this->_percent_billed = floatval( $percent );
		} else {
			$this->_validation_errors[ 'percent_billed' ] = __( 'The percent billed must be a numeric value.' );
		}
	}
		
	/**
	 * Gets the slug for this project.
	 *
	 * @return string
	 */
	function get_project_slug() {
		return $this->_slug;
	}
	
	/**
	 * Returns the title for this project.
	 *
	 * @return string
	 */
	function get_project_title() {
		return $this->_title;
	}
	
	/**
	 * Sets the title for this project.
	 *
	 * @param string $title
	 */
	function set_project_title( $title ) {
		$this->_title = $title;
		if( $this->_slug === null ) {
			$this->_slug = sanitize_title_with_dashes( $this->_title );
		}
	}
  
	/**
	 * Returns an array of WP_Project_Task objects that are used in this project.
	 *
	 * This implements lazy loading so it only fetches the tasks if they're needed the
	 * first time this is called.
	 * 
	 * @return array
	 */
	function get_tasks() {
		if( $this->_tasks === null ) {
			$this->_tasks = WP_Project_Task::get_tasks_by_project( $this->_id );
		}
		
		return $this->_tasks;
	}
	
}

?>