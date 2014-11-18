<?php

class WP_Central_Shortcodes {

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

		return rockstar_graph( 'morris', 'line_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys ) );
	}

	public function versions_last_year_php() {
		$data = WordPress_Stats_Api::php_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return rockstar_graph( 'morris', 'line_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys ) );
	}

	public function versions_last_year_mysql() {
		$data = WordPress_Stats_Api::mysql_version_by_day();
		$keys = array_keys( (array) end( $data) );
		unset( $keys[0] );
		$keys = array_values( $keys );

		return rockstar_graph( 'morris', 'line_chart', $data, array( 'x' => 'date', 'y' => $keys, 'label' => $keys ) );
	}


	public function current_wordpress_versions() {
		return rockstar_graph( 'morris', 'pie_chart', WordPress_Stats_Api::wordpress_version() );
	}

	public function current_php_versions() {
		return rockstar_graph( 'morris', 'pie_chart', WordPress_Stats_Api::php_version() );
	}

	public function current_mysql_versions() {
		return rockstar_graph( 'morris', 'pie_chart', WordPress_Stats_Api::mysql_version() );
	}

}

new WP_Central_Shortcodes;