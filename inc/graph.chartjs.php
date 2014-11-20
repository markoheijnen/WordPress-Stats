<?php

/**
 * Chart.js graphs
 */
class Rockstar_Graph_Chartjs extends Rockstar_Graph_Abstract {

	public function register_script() {

		wp_register_script( 'chartjs', plugins_url( '/js/Chart.min.js', dirname( __FILE__ ) ), array(), '1.0.1-beta.4' );
		wp_enqueue_script( 'chartjs' );
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function line_chart( $args ) {

		$data = array(
			'labels'   => wp_list_pluck( $this->data, 'label' ),
			'datasets' => array(
				array(
					'data'      => wp_list_pluck( $this->data, 'value' ),
					'fillColor' => 'rgba(11,98,164,0.5)',
				),
			),
		);

		return $this->get_chart( 'Line', $data, $args );
	}

	/**
	 * @param array $args
	 */
	public function pie_chart( $args ) {
	}

	/**
	 * @param array $args
	 */
	public function bar( $args ) {
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function radar_chart( $args ) {

		$data = array(
			'labels'   => wp_list_pluck( $this->data, 'label' ),
			'datasets' => array(
				array(
					'data'      => wp_list_pluck( $this->data, 'value' ),
					'fillColor' => 'rgba(11,98,164,0.5)',
				),
			),
		);

		return $this->get_chart( 'Radar', $data, $args );
	}

	/**
	 * @param string $type
	 * @param array  $args
	 *
	 * @return string
	 */
	protected function get_chart( $type, $data, $args ) {

		$uid  = esc_attr( $this->unique_id() );
		$type = esc_js( $type );
		$html = '<canvas id="' . $uid . '" class="graph"></canvas>';

		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';

		$options = array(
			'tooltipTemplate' => "<%= Math.floor(value/1000000) %> M",
			'responsive'      => true,
		);

		if ( isset( $args['options'] ) ) {
			$options = array_merge( $options, $args['options'] );
		}

		$html .= 'var data' . $uid . ' = ' . json_encode( $data ) . ";\n";
		$html .= 'var ctx' . $uid . ' = document.getElementById("' . $uid . '").getContext("2d");' . "\n";
		$html .= 'var ' . $type . 'Chart' . $uid . ' = new Chart(ctx' . $uid . ').' . $type . '(data' . $uid . ',' . json_encode( $options ) . ');' . "\n";
		$html .= '});';
		$html .= '</script>';

		return $html;
	}
}
