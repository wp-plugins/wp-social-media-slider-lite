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
		$string_data = print_r( $data, true );
		$file_path = explode( '\\' , $call_info[0]['file'] );
		$filename = array_pop( $file_path );
		error_log(  $filename . ' on line ' . $call_info[0]['line'] . ': ' . $string_data );
	}

}