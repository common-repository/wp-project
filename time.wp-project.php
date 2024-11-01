<?php

/**
 * This class represents a time entry in the WP-Project system.
 */
class WP_Project_Time {
	
	/**
	 * Toggles a timer being on/off.
	 * 
	 * If the timer id passed is already stored in the system, then the timer is stopped and
	 * the current timer id is reset.  If the timer id passed is different from the one 
	 * currently in the system and no timer is currently running, then the timer is started
	 * for the specified timer id.  If a timer is running and a new timer id is passed, then
	 * the old timer is stopped, the necessary time is added to the time for the old time, and
	 * the new timer is started.
	 *
	 * @param int $timer_id the unique identifier of the timer to toggle.
	 * @return int the id of the currently running timer.
	 */
	function toggle( $timer_id, $user_id ) {
		$current_timer_id = intval( $current_timer_id );
		$new_timer_id = intval( $new_timer_id );
		
		
		if( $current_timer == $new_timer ) {
			$this->options[ 'current_timer' ] = -1;
			$this->options[ 'timer_started' ] = 0;				
			
		} else {
			$this->options[ 'current_timer' ] = $new_timer;
			$this->options[ 'timer_started' ] = $new_timer_started;
			
		}
		
		
		update_option( 'WP-Project Options', serialize( $this->options ) );
		$this->add_seconds_to_task( $current_timer, $new_timer_started - $current_timer_started );
		
		echo $new_timer;
	}
	
}

?>