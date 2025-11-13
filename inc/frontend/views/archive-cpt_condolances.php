<?php get_header(); ?>

<div class="wrap container">
	<?php if (have_posts()) : ?>
	<div id="condolances-content" class="condolances-content">
	<?php while (have_posts()) : the_post(); ?>
		<article class="hentry person-hentry">
			<div class="person-thumb">
				<?php the_post_thumbnail('condo-thumbnail'); ?>
			</div>

			<main class="person-hentry-main">
				<h3 class="entry-title">
					<a href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
					</a>
				</h3>
				
				<div class="person-meta">
					<ul>
						<li>Geboortedatum <strong><?php echo get_post_meta(get_the_ID(), 'cmb_condalances_birthday', true); ?></strong></li>
						<li>Overlijdensdatum <strong><?php echo get_post_meta(get_the_ID(), 'cmb_condalances_deathday', true); ?></strong></li>
						<li>Aantal reacties <strong><?php echo get_comments_number(); ?></strong></li>
					</ul>
				</div>

				<div class="excerpt">
					<?php the_excerpt(); ?>
				</div>
			</main>
		</article>
		<?php endwhile; ?>
	</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>