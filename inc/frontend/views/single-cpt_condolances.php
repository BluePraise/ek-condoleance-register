<?php get_header(); ?>

<div class="wrap container">
	<?php while (have_posts()) : the_post(); ?>
	<h1><?php the_title(); ?></h1>

	<div id="condolances-content" class="condolances-content">
		<?php the_content(); ?>
	</div>
	<ol class="comment-list">
    <?php
    	do_action( 'tahlil_reactie_form' );
        comments_template();
    ?>
	</ol>
	<?php // include_once('reacties-form.php'); ?>

	<?php endwhile; ?>
</div>


<?php get_footer(); ?>