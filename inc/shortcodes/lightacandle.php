<?php

function light_a_candle($atts)
{
	$a = shortcode_atts( array(
		'post_id' => get_the_ID(),
		'be_the_first' => '',
		'btn_text' => 'Kaarsje Aansteken'
	), $atts );

	$print = '';
	ob_start();

	include 'candle.php';

	$p = ob_get_contents();
	ob_end_clean();
	$print .= $p;
	return $print;
}
add_shortcode('light_a_candle', 'light_a_candle');