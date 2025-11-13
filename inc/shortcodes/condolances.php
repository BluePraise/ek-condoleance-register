<?php

	function TahlilPagination($pages = '', $range = 4)
	{  
	     $showitems = ($range * 2)+1;  
	     // echo $showitems . '<br />';
	     // echo $pages;
	 
	     global $paged;
	     if(empty($paged)) $paged = 1;
	 
	     if($pages == '')
	     {
	         global $wp_query;
	         $pages = $wp_query->max_num_pages;
	         if(!$pages)
	         {
	             $pages = 1;
	         }
	     }   
	 
	     if(1 != $pages)
	     {
	         echo "<div class=\"pagination gem-pagination\"><div class=\"gem-pagination-links\"><!--<span>Page ".$paged." of ".$pages."</span>-->";
	         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a class=\"page-numbers\" href='".get_pagenum_link(1)."'>&laquo;</a>";
	         // if($paged > 1 && $showitems < $pages) 
	         if($paged > 1) 
	         	echo "<a class=\"prev page-numbers\" href='".get_pagenum_link($paged - 1)."'>&lsaquo;</a>";
	 
	         for ($i=1; $i <= $pages; $i++)
	         {
	             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
	             {
	                 echo ($paged == $i)? "<span class=\"current page-numbers\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
	             }
	         }
	 
	         // if ($paged < $pages && $showitems < $pages) 
	         if ($paged < $pages) 
	         	echo "<a class=\"next page-numbers\" href=\"".get_pagenum_link($paged + 1)."\">&rsaquo;</a>";  
	         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a class=\"page-numbers\" href='".get_pagenum_link($pages)."'>&raquo;</a>";
	         echo "</div></div>\n";
	     }
	}

	function register_condolances($atts) {
		$a = shortcode_atts( array(
    		'per_page' => 10,
    		'pagination' => 1,
    		'type' => 'cpt_condolances',
		), $atts );

	    $print = '';
	    
	    ob_start();
	    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	    $args = array(
	        'post_type'=> $a['type'], 
	        'posts_per_page'=>$a['per_page'], 
	        'paged'=>$paged
	    );
	    $posts = new WP_Query($args);
	    while($posts->have_posts()): $posts->the_post();
	    include 'loop-cpt_condolances.php';
	    endwhile; wp_reset_query();
	    if($a['pagination'] == 1) 
	        TahlilPagination($posts->max_num_pages, $a['per_page']);
	    $p_content = ob_get_contents();
	    ob_end_clean();
	    $print .= '<div id="condolances-content" class="condolances-content blog blog-style-compact clearfix item-animation-move-up">';
	    $print .= $p_content;
	    $print .= '</div>';
	    wp_reset_query();
	    
	    return $print;
	}
	add_shortcode('register_condolances', 'register_condolances');