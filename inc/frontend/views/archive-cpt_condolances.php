<?php get_header(); ?>

<div class="wrap container">
	<?php if (have_posts()) : ?>
	<div id="condolances-content" class="condolances-content">
	<?php while (have_posts()) : the_post(); ?>
		<article class="hentry person-hentry">
			<div class="person-thumb">
				<?php the_post_thumbnail('thumbnail'); ?>
			</div>

			<main class="person-hentry-main">
				<h3 class="entry-title">
					<a href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
					</a>
				</h3>
				
				<div class="person-meta">
					<ul>
						<li>Date of Birth: <strong><?php echo get_post_meta(get_the_ID(), 'cmb_condalances_birthday', true); ?></strong></li>
						<li>Date of Death: <strong><?php echo get_post_meta(get_the_ID(), 'cmb_condalances_deathday', true); ?></strong></li>
						<li>Number of Reacties: <strong><?php echo get_comments_number(); ?></strong></li>
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


<style type="text/css">
	.person-hentry {
		margin-bottom: 42px;
		position: relative;
		border: 1px solid #e9e9e9;
	}

	.person-hentry-main {
		padding: 12px 12px 12px 170px;
	}

	.person-thumb {
		position: absolute;
		left: 0;
		top: 0;
		width: 150px;
	}

	.person-hentry .entry-title {
		margin-top: 0;
	}

	.person-meta ul {
		list-style-type: none;
		margin-left: 0;
		padding: 0 0 0 0;
		margin: 0 0 0 0;
	}

	.person-meta {
		font-size: 11px;
		margin-bottom: 20px;
	}
</style>

<?php get_footer(); ?>