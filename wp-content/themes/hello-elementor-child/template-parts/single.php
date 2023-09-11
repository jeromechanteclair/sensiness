<?php
/**
 * The template for displaying singular post-types: posts, pages and user-defined custom post types.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$summary = get_post_meta(get_the_id(),'summary',true);
$category = get_the_category(  );
$tags = get_the_tags(  );

$desktop = get_the_post_thumbnail_url($post, 'single_hero');
$mobile = get_the_post_thumbnail_url($post, 'single_hero');

$related_query = new WP_Query(array(
    'post_type' => 'post',
    'category__in' => wp_get_post_categories(get_the_ID()),
    'post__not_in' => array(get_the_ID()),
    'posts_per_page' => 3,
    'orderby' => 'date',
));

$frontend_summary = get_post_meta($post->ID, 'frontend_summary', true);


while ( have_posts() ) :
	the_post();
	?>
<header class="single-header">
	<div class="container">
		<div class="single-header__left">
			<?php if (function_exists('yoast_breadcrumb')) {
				yoast_breadcrumb('<p class="woocommerce-breadcrumb">', '</p>');
			}
			?>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<div class="single-header__metas">
				<a href="<?= get_category_link( $category[0]);?>"><?= $category[0]->name;?></a>
				<span class="separator"></span>
				<time><?= the_date();?></time>
				<span class="separator"></span>
				<span><?= reading_time();?></span>
			</div>
		</div>
		<picture class="single-header__right">
			
			<source srcset="<?=$desktop;?>" media="(min-width: 999px)">
			<source srcset="<?=$mobile;?>" media="(max-width: 999px)">

			<img src="<?=$desktop;?>" alt="<?= the_title();?>">

		</picture>
</header>
<div class="article-container container">



	<main id="content" <?php post_class( 'site-main container container--min' ); ?>>
		<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
	
		<?php endif; ?>
		<div class="page-content">
			<?php the_content(); ?>
			<div class="post-tags">
				<?php the_tags( '<span class="tag-links">' . esc_html__( 'Tagged ', 'hello-elementor' ), null, '</span>' ); ?>
			</div>
			<?php wp_link_pages(); ?>
		</div>

		
	</main>
		<aside class="scrollbar">
			
		<?php if(!empty($frontend_summary)):?>
			<p class="title">Sommaire :</p>
			<ul class="summary">

			
			<?php foreach($frontend_summary as $link):
				$anchor = get_the_permalink().'#'.$link['id'];
				if(isset($link['is_display'])):
				?>
				<li >
					<a href="<?= $anchor;?>">
						<?= wp_strip_all_tags($link['content']);?>
					</a>
				</li>
			<?php endif;endforeach;?>
			</ul>
		<?php endif;?>
	

	</aside>
	<div class="sticky-nl">
		<?php 
		$nl_fields=get_field('newsletter','options');?>
		<p class="title"><?=$nl_fields['newsletter_post_title'];?></p>
		<p class="subtitle"><?=$nl_fields['newsletter_post_subtitle'];?></p>
		<?php echo do_shortcode('[probance_newsletter]');?>
			<div class="sharing">
			<p class="title">Partager l'article :</p>
			<div class="social-buttons">
					<a href="https://twitter.com/intent/tweet?text=<?=get_the_permalink();?>" >
				<?php get_template_part('template-parts/svg/twitter.svg');?>

					</a>
					<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?=get_the_permalink();?>" >
					
					<?php get_template_part('template-parts/svg/linkedin.svg');?>
					</a>
					<a href="https://www.facebook.com/sharer/sharer.php?u=<?=get_the_permalink();?>" >

					<?php get_template_part('template-parts/svg/facebook.svg');?>
					</a>
				</div>
		</div>
	
	</div>			
</div>
<?php if ($related_query->have_posts()) { ?>
<div class="related-posts">
	<div class="container ">
		<h2>Lire aussi</h2>
    <div class="related-posts-grid">

        <?php while ($related_query->have_posts()) { ?>

            <?php $related_query->the_post(); ?>
				<?php get_template_part( 'template-parts/single','post' );?>
      

        <?php } ?>

    </div>

    <?php wp_reset_postdata(); ?>
</div>
</div>
<?php } ?>
<div class="single-comments">
	<div class="container container--min">

		<?php comments_template();?>
	</div>

</div>
	<?php
endwhile;
