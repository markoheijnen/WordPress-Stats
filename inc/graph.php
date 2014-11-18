<?php

function wordpress_stats_graph( $type, $method, $data, $args = array() ) {
	global $wordpress_stats_graph;

	return $wordpress_stats_graph->graph( $type, $method, $data, $args );
}

class WordPress_Stats_Graph {

	public function __construct() {
		add_action( 'init', array( $this, 'register_script' ) );
	}

	public function register_script() {
		wp_register_script( 'raphael', plugins_url( '/js/raphael.min.js', dirname( __FILE__ ) ), array() );
	}

	public function graph( $type, $method, $data, $args = array() ) {
		$class_name = 'Rockstar_Graph_' . $type;

		if( class_exists( $class_name ) ) {
			$graph = new $class_name( $data );

			if( ! is_callable( array( $graph, $method ) ) ) {
				return false;
			}

			return call_user_func( array( $graph, $method ), $args );
		}

		return false;
	}
}

abstract class Rockstar_Graph_Abstract {
	protected $data = array();
	protected static $counter = 0;

	public function __construct( $data ) {
		self::$counter++;

		$this->data = $data;

		$this->register_script();
	}

	final protected function unique_id() {
		return 'graph' . self::$counter;
	}

	abstract public function line_chart( $args );
	abstract public function pie_chart( $args );
	abstract public function bar( $args );
}

$wordpress_stats_graph = new WordPress_Stats_Graph();
