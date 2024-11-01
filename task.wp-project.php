<?php

/**
 * This class represents a task in the WP-Project system.
 */
class WP_Project_Task {
	
	/**
	 * Returns a WP_Project_Task from the WP-Project system that is uniquely identified
	 * by id.
	 *
	 * @param int $id
	 * @return WP_Project_Task
	 */
	function get( $id ) {
		global $wpdb;
		
		// Clean up the argument
		$id = $wpdb->escape( intval( $id ) );
		
		$result = $wpdb->get_row( "SELECT * FROM $wpdb->task_table WHERE id = $id", OBJECT );
		
		// Initialize the task object
		$task = new WP_Project_Task();
		
		return $task;
	}
	
	/**
	 * Returns an array of tasks that belong to a specific project, as identified
	 * by the project id.
	 *
	 * @param int $project_id
	 * @return array
	 */
	function get_tasks_by_project( $project_id ) {
		global $wpdb;
		
		// Clean up the argument
		$project_id = $wpdb->escape( intval( $project_id ) );
		
		$results = $wpdb->get_results( "", OBJECT );
	}
}

?>