<?php

class WordPress_Stats_Shortcodes {

	public function __construct() {
		add_shortcode( 'versions_last_year_wordpress', array( $this, 'versions_last_year_wordpress' ) );
		add_shortcode( 'versions_last_year_php', array( $this, 'versions_last_year_php' ) );
		add_shortcode( 'versions_last_year_mysql', array( $this, 'versions_last_year_mysql' ) );

		add_shortcode( 'current_wordpress_versions', array( $this, 'current_wordpress_versions' ) );
		add_shortcode( 'current_php_versions', array( $this, 'current_php_versions' ) );
		add_shortcode( 'current_mysql_versions', array( $this, 'current_mysql_versions' ) );
	}


	public function versions_last_year_wordpress() {
		$data = WordPress_Stats_Api::wordpress_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return wordpress_stats_graph( 'morris', 'area_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys, 'ymax' => 100 ) );
	}

	public function versions_last_year_php() {
		$data = WordPress_Stats_Api::php_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return wordpress_stats_graph( 'morris', 'area_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys, 'ymax' => 100 ) );
	}

	public function versions_last_year_mysql() {
		$data = WordPress_Stats_Api::mysql_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return wordpress_stats_graph( 'morris', 'area_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys, 'ymax' => 100 ) );
	}

	/**
	 * @return bool|string
	 */
	public function current_wordpress_versions() {
		return wordpress_stats_graph( 'chartjs', 'doughnut_chart', WordPress_Stats_Api::wordpress_version() );
	}

	/**
	 * @return bool|string
	 */
	public function current_php_versions() {
		return wordpress_stats_graph( 'chartjs', 'doughnut_chart', WordPress_Stats_Api::php_version() );
	}

	/**
	 * @return bool|string
	 */
	public function current_mysql_versions() {
		return wordpress_stats_graph( 'chartjs', 'doughnut_chart', WordPress_Stats_Api::mysql_version() );
	}

}