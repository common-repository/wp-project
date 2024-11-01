<?php

/**
 * This class represents a client in the WP-Project system.
 */
class WP_Project_Client {
	
	
	/**
	 * Removes a user as a member of all clients they are a part of.
	 *
	 * @param int $user_id
	 */
	function remove_user_from_all_clients( $user_id ) {
		global $wpdb;
		
		$user_id = $wpdb->escape( intval( $user_id ) );
		
		$wpdb->query( "DELETE FROM $wpdb->client_member_table WHERE user_id = $user_id" );
	}
	
	/**
	 * Removes a user as a member of all clients they are a part of.
	 *
	 * @param int $user_id
	 */
	function remove_user_from_client( $user_id, $client_id ) {
		global $wpdb;
		
		$user_id = $wpdb->escape( intval( $user_id ) );
		$client_id = $wpdb->escape( intval( $client_id ) );
				
		$wpdb->query( "DELETE FROM $wpdb->client_member_table WHERE user_id = $user_id AND client_id = $client_id" );
	}
}

?>