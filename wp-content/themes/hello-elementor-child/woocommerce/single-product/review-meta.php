<?php
/**
 * The template to display the reviewers meta data (name, verified owner, review date)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/review-meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $comment;
$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

if ( '0' === $comment->comment_approved ) { ?>

	<p class="meta">
		<em class="woocommerce-review__awaiting-approval">
			<?php esc_html_e( 'Your review is awaiting approval', 'woocommerce' ); ?>
		</em>
	</p>

<?php } else { ?>

	<p class="meta">
		<strong class="woocommerce-review__author">
			<svg xmlns="http://www.w3.org/2000/svg" width="27.411" height="26.377" viewBox="0 0 27.411 26.377">
  <path id="Tracé_2785" data-name="Tracé 2785" d="M118.258,145.309a2.633,2.633,0,0,0-1.651-1.432l.014,0a1.52,1.52,0,0,1-.771-2.391A2.632,2.632,0,0,0,112.9,137.4a1.524,1.524,0,0,1-2.038-1.477,2.632,2.632,0,0,0-4.8-1.561,1.526,1.526,0,0,1-2.518,0,2.632,2.632,0,0,0-4.8,1.561,1.524,1.524,0,0,1-2.037,1.477,2.632,2.632,0,0,0-2.953,4.087,1.52,1.52,0,0,1-.779,2.391,2.632,2.632,0,0,0,0,5.05,1.525,1.525,0,0,1,.728,2.4,2.632,2.632,0,0,0,2.953,4.083,1.524,1.524,0,0,1,2.038,1.476,2.632,2.632,0,0,0,4.8,1.562,1.551,1.551,0,0,1,2.543,0,2.584,2.584,0,0,0,2.141,1.152,2.724,2.724,0,0,0,.845-.136,2.584,2.584,0,0,0,1.817-2.584,1.525,1.525,0,0,1,2.038-1.477,2.632,2.632,0,0,0,2.953-4.082,1.524,1.524,0,0,1,.779-2.4,2.633,2.633,0,0,0,1.651-3.618Zm-17.2.833a.554.554,0,0,1,1.011-.26l2.086,2.677,3.278-5.571a.553.553,0,0,1,.952.561l-3.691,6.275a.563.563,0,0,1-.439.274h-.037a.55.55,0,0,1-.436-.214l-2.584-3.322a.553.553,0,0,1-.142-.37C101.061,146.175,101.061,146.158,101.063,146.142Z" transform="translate(-91.085 -133.225)" fill="#364321"></path>
</svg>&nbsp;<?php comment_author(); ?> </strong>
		<?php
		if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) {
			echo '<em class="woocommerce-review__verified verified">(' . esc_attr__( 'verified owner', 'woocommerce' ) . ')</em> ';
		}

		?>
		<span class="woocommerce-review__dash">&ndash;</span> <time class="woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></time>
	</p>
	<p>Acheteur vérifié</p>

		
	<?php
}
