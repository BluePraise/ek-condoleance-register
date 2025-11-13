<?php get_header(); ?>

<div id="main-content" class="main-content">

	<?php while (have_posts()) : the_post(); ?>

	<?php 
		 //do_action( 'reactie_comment_content' );
		 //do_action( 'tahlil_reactie_form' ); 
		get_template_part('condolence-single.php');
		// get_template_part( 'template-parts/post/content', get_post_format() );
	?>

	<?php endwhile; ?>
	
</div>

<?php get_footer(); ?>