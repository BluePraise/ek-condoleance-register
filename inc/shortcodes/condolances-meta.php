<?php

function condolances_meta($atts)
{
	$a = shortcode_atts( array(
		'post_id' => get_the_ID()
	), $atts );

	$print = '';
	ob_start();

	include 'view-condolances-meta.php';

	$p = ob_get_contents();
	ob_end_clean();
	$print .= $p;
	return $print;
}
add_shortcode('condolances_meta', 'condolances_meta');