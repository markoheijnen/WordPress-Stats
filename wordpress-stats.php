<?php
/*
Plugin Name: WordPress stats
Plugin URI:  http://wpcentral.io
Description: Retrieves all stats provided by WordPress.org
Author:      Marko Heijnen
Text Domain: wordpress-stats
Version:     1.0
Author URI:  http://markoheijnen.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

include 'api.php';

include 'inc/graph.php';
include 'inc/graph.morris.php';
include 'inc/shortcodes.php';


class WordPress_Stats {
	private $version = '1.0';
	private $api_url = 'http://api.wordpress.org/stats/';

	private $db_table;
	private $request_time;

	public function __construct() {
		$this->db_table = WordPress_Stats_Api::db_table();

		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		add_action( 'cron_wordpress_stats', array( $this, 'cronjob_fast' ) );
		add_action( 'cron_wordpress_stats_daily', array( $this, 'cronjob_daily' ) );

		new WordPress_Stats_Shortcodes;
	}

	public function install() {
		global $wpdb;

		if( $wpdb->get_var( "SHOW TABLES LIKE '" . $this->db_table . "'" ) != $this->db_table ) {
			$sql = "CREATE TABLE IF NOT EXISTS " . $this->db_table . " (
				type varchar(32) NOT NULL,
				version varchar(8) NOT NULL,
				count DECIMAL(10,1) NOT NULL,
				date_gmt datetime NOT NULL
			);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta( $sql );
		}

		if ( ! wp_next_scheduled( 'cron_wordpress_stats' ) ) {
			date_default_timezone_set("UTC");
			$time = time();
			$time = ceil( $time / ( 1 * 60 ) ) * ( 1 * 60 );

			wp_schedule_event( $time, 'minutly', 'cron_wordpress_stats' );

			wp_schedule_event( strtotime( 'today 7am' ), 'daily', 'cron_wordpress_stats_daily' );
		}
	}

	public function deactivate() {
		wp_clear_scheduled_hook( 'cron_wordpress_stats' );
		wp_clear_scheduled_hook( 'cron_wordpress_stats_daily' );
	}




	public function cron_schedules( $schedules ) {
		$schedules['minutly'] = array( 'interval' => MINUTE_IN_SECONDS, 'display' => __( 'Every minute', 'wordpress-stats' ) );

		return $schedules;
	}

	// Type = Downloads, MySQL, PHP, WordPress
	public function cronjob_fast() {
		$this->add_stat( 'downloads', $this->latest_version(), $this->get_current_wordpress_downloads() );
	}

	public function cronjob_daily() {
		$wordpress = $this->get_current_wordpress_usage();
		foreach( $wordpress as $version => $percentage ) {
			$this->add_stat( 'wordpress', $version, $percentage );
		}

		$php = $this->get_current_php_usage();
		foreach( $php as $version => $percentage ) {
			$this->add_stat( 'php', $version, $percentage );
		}

		$mysql = $this->get_current_mysql_usage();
		foreach( $mysql as $version => $percentage ) {
			$this->add_stat( 'mysql', $version, $percentage );
		}

		// Delete the caches
		delete_transient('wordpress_versions');
		delete_transient('php_versions');
		delete_transient('mysql_versions');
	}



	public static function latest_version() {
		if ( false === ( $version = get_transient( 'wordpress_version' ) ) ) {
			$request = wp_remote_get( 'http://api.wordpress.org/core/version-check/1.7/' );
			$data    = json_decode( wp_remote_retrieve_body( $request ) );

			if( $data ) {
				$version = explode( '.', $data->offers[0]->current );
				$version = $version[0] . '.' . $version[1];

				set_transient( 'wordpress_version', $version, 3600 * 6 );
			}
		}

		return $version;
	}

	public static function get_current_wordpress_downloads() {
		$response = wp_remote_get( 'http://wordpress.org/download/counter/?ajaxupdate=1&time=' . time() );

		if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			self::report_error( $response );

			return false;
		}
		else {
			$count = wp_remote_retrieve_body( $response );

			$count = str_replace( '.', '', $count );
			$count = str_replace( ',', '', $count );
			
			return $count;
		}
	}

	public function get_current_wordpress_usage() {
		$response = wp_remote_get( $this->api_url . 'wordpress/1.0/' );

		if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->report_error( $response );

			return false;
		}
		else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			return $data;
		}
	}

	public function get_current_php_usage() {
		$response = wp_remote_get( $this->api_url . 'php/1.0/' );

		if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->report_error( $response );

			return false;
		}
		else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			return $data;
		}
	}

	public function get_current_mysql_usage() {
		$response = wp_remote_get( $this->api_url . 'mysql/1.0/' );

		if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->report_error( $response );

			return false;
		}
		else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			return $data;
		}
	}



	public function add_stat( $type, $version, $count ) {
		global $wpdb;

		if( ! $this->request_time ) {
			$this->request_time = get_gmt_from_date( current_time( 'mysql' ) );
		}

		$data             = array();
		$data['type']     = $type;
		$data['version']  = $version;
		$data['count']    = $count;
		$data['date_gmt'] = $this->request_time;
		
		$wpdb->insert( $this->db_table, $data, array( '%s', '%s', '%f', '%s' ) );
	}


	private static function report_error( $response ) {
		
	}

}

new WordPress_Stats;