<?php get_header(); ?>

<div id="main-content" class="main-content">

	<?php while (have_posts()) : the_post(); ?>

	<?php 
		$candles = get_post_meta(get_the_ID(), 'cmb_condalances_candles', 1);

		$string = ' Kaarsen zijn bliksem...';

		if ($candles == 1) {
			$string = ' Kaarsje is bliksem...';
		}

		do_action( 'reactie_comment_content' );
		do_action( 'tahlil_reactie_form' ); 
		include_once( 'content-cpt_condolances.php' );
	?>

	<h1><?php the_title(); ?></h1>

	<div id="condolances-content" class="condolances-content">
		<div class="person-gallery">
			<h3 id="light_a_candle_response_count">
				<?php if(!$candles) : ?> 
					Be the first to light a candle..
				<?php else: ?>
					<?php echo $candles; ?> <?php echo $string; ?>
				<?php endif; ?>
			</h3>

			<p id="light_a_candle_response"></p>

			<button id="light_a_candle" data-id="<?php echo get_the_ID(); ?>" class="gem-button">Light a candle</button>

			<h3>Album</h3>

			<?php $photos = get_post_meta(get_the_ID(), 'cmb_condalances_photos', true); ?>
			<?php foreach($photos as $id => $photo) : ?>
				<a href="<?php echo $photo; ?>">
					<?php echo wp_get_attachment_image( $id, 'thumbnail' ); ?>
				</a>
			<?php endforeach; ?>

			
		</div>
		<?php the_content(); ?>
	</div>
	<ol class="comment-list">
    <?php
		
    	comments_template();
    ?>
	</ol>

	<?php endwhile; ?>
	
</div>
</div>


<style type="text/css">
	.person-gallery img {
		width: 230px;
		display: inline-block;
		margin: 10px 20px 20px 0;
	}
</style>

<?php get_footer(); ?>