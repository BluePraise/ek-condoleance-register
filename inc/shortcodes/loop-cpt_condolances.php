<article <?php echo post_class('no-image clearfix thegem_news type-thegem_news thegem_news_sets-uitvaartnieuws item-animations-inited'); ?>>
	<div class="gem-compact-item-left">
		<div class="gem-compact-item-image">
			<a class="default" href="<?php echo esc_url(get_permalink()); ?>">
				<img src="<?php the_post_thumbnail_url('full'); ?>">
			</a>
		</div>
	</div>

	<div class="gem-compact-item-right">
		<div class="gem-compact-item-content">
			<div class="post-title">
				<h5 class="entry-title reverse-link-color">
					<a href="<?php the_permalink(); ?>">
						<?php //echo get_the_date('d M Y'); ?>
						<span class="light">
							<?php the_title(); ?>
						</span>
					</a>
				</h5>
			</div>
			
			<div class="post-text">
				<div class="summary">
					<ul class="reactie-meta">
						<li><strong>Geboortedatum:</strong> 
							<?php $originalDate = get_post_meta(get_the_ID(), 'cmb_condalances_birthday', true); 
$newDate = date("d/m/Y", strtotime($originalDate));
							echo $newDate; ?></li>
						<li><strong>Overlijdensdatum:</strong> <?php 
							
							$originalDate = get_post_meta(get_the_ID(), 'cmb_condalances_deathday', true); 
							$newDate = date("d/m/Y", strtotime($originalDate));
							echo $newDate;
							?></li>
						<li><strong>Aantal Reacties:</strong> <?php echo get_comments_number(); ?></li>
					</ul>
					<div class="excerpt">
						<?php the_excerpt(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</article>

<style type="text/css">

/* COMPACT */
h5.reverse-link-color {
	color: #3c3950;
}

.reverse-link-color a {
	color: inherit;
}

.reverse-link-color a:hover {
	color: #b2201e;
}

.summary ul {
	list-style-type: none;
	padding: 0 0 0 0;
	margin: 0 0 0 0;
	font-size: 13px;
}

.summary ul li {
	display: inline-block;
	margin-right: 8px;
}

.gem-compact-item-image img {
	width: 100%;
	height: auto;
}

body .blog-style-compact article a.default:after {
	display: none;
}

.blog-style-compact article {
	padding-left: 212px;
	position: relative;
}
.blog-style-compact article + article {
	margin-top: 40px;
}
.gem-compact-item-left {
	float: left;
	margin-left: -212px;
	width: 183px;
}
.gem-compact-item-image .gem-dummy {
	width: 183px;
	height: 148px;
	border-radius: 0;
	vertical-align: top;
}
.gem-compact-item-right {
	position: absolute;
	left: 212px;
	right: 0;
	top: 0;
	height: 100%;
}
.blog-style-compact article .gem-compact-item-content {
	position: absolute;
	top: 0;
	bottom: 0;
	overflow: hidden;
}
.blog-style-compact article .gem-compact-item-content:after {
	content: '';
	position: absolute;
	bottom: 0;
	left: 0;
	width: 100%;
	height: 0;
}
.blog-style-compact article .post-title h5 {
	margin-top: -7px;
}
.blog-style-compact article .post-meta {
	position: absolute;
	bottom: 0;
	margin: 0;
	width: 100%;
}
.blog-style-compact article .post-meta .entry-meta {
	margin: 0;
	font-size: 13px;
	line-height: 20px
}

body .blog article a.default:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transition: all 0.2s linear;
    -moz-transition: all 0.2s linear;
    -webkit-transition: all 0.2s linear;
    opacity: 0;
    z-index: 5;
}

body .blog-style-compact article a.default:after {
	content: '\e628';
	border-radius: 50%;
	font-size: 16px;
	top: 150%;
	margin-top: -24px;
	margin-left: -24px;
	opacity: 0;
	-webkit-transform: scale(0);
	transform: scale(0);
	-webkit-transition: top 0.4s, opacity 0.4s, -webkit-transform 0s 0.4s;
	transition: top 0.4s, opacity 0.4s, transform 0s 0.4s;
}
body .blog-style-compact article a.default:hover:after {
	opacity: 1;
	-webkit-transform: scale(1);
	transform: scale(1);
	-webkit-transition: top 0s, opacity 0.4s, -webkit-transform 0.4s;
	transition: top 0s, opacity 0.4s, transform 0.4s;
}
@media (max-width: 599px) {
	.blog-style-compact article {
		padding-left: 0;
	}
	.gem-compact-item-left {
		float: none;
		margin-left: 0;
		width: auto;
		text-align: center;
	}
	.gem-compact-item-right {
		position: relative;
		left: auto;
		right: auto;
		top: auto;
		height: auto;
	}
	.blog-style-compact article .gem-compact-item-content {
		position: relative;
		top: auto;
		bottom: auto;
		overflow: hidden;
		margin-top: 30px;
	}
	.blog-style-compact article .gem-compact-item-content:after {
		display: none;
	}
	.blog-style-compact article .post-meta {
		position: relative;
		bottom: auto;
		margin: 0;
		width: auto;
	}
}
</style>