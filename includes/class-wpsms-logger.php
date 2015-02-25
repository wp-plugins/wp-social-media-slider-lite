<?php

/**
 * Log some data to the debug.log
 *
 * @since      1.0.6
 * @package    Wpsms
 * @subpackage Wpsms/includes
 * @author     Pete Molinero <pete@laternastudio.com>
 */

class Wpsms_Logger {

	/**
	 * Log to file
	 *
	 * @since   1.0.6
	 * @return  void
	 */
	public function output( $data ) {
		$call_info = debug_backtrace();
		$file_path = explode( '\\' , $call_info[0]['file'] );
		$filename = array_pop( $file_path );

		ob_start();
			echo $filename . ' on line ' . $call_info[0]['line'] . ': ';
			var_dump( $data );

		$output = ob_get_contents();
		ob_end_clean();

		error_log( $output );
	}

}