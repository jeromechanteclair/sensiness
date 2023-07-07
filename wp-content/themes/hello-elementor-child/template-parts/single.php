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

$related_query = new WP_Query(array(
    'post_type' => 'post',
    'category__in' => wp_get_post_categories(get_the_ID()),
    'post__not_in' => array(get_the_ID()),
    'posts_per_page' => 3,
    'orderby' => 'date',
));

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
		<?php the_post_thumbnail();?>
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
	<aside>
		<?php if(!empty($summary)):?>
			<h2>Dans cet article :</h2>
			<ul class="summary">

			
			<?php foreach($summary as $link):
				$anchor = get_the_permalink().'#'.$link['id'];
				?>
				<li class="<?=$link['balise']?>">
					<a href="<?= $anchor;?>">
						<?= ucfirst(mb_strtolower($link['contenu']));?>
					</a>
				</li>
			<?php endforeach;?>
			</ul>
		<?php endif;?>

	</aside>

</div>
<?php if ($related_query->have_posts()) { ?>
<div class="related-posts">
	<div class="container container--min">
    <div class="related-posts-grid">

        <?php while ($related_query->have_posts()) { ?>

            <?php $related_query->the_post(); ?>

          <?= the_title();?>

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
