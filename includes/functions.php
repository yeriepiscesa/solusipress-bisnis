<?php

function solusipress_format_money( $float ) {
	
	$s = number_format( $float, 0, ',', '.' );
	if( $float < 0 ) {
		$s = '(' . $s . ')';
	}
	return $s;
	
}