<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews" class="woocommerce-Reviews">
	<div class="container container--min">
	<div id="comments">
		<h2 class="woocommerce-Reviews-title">
			Les avis de nos clients
		</h2>
		<header class="reviews-header">
			<div class="reviews-header__left">
				<p><?= $product->get_average_rating();?>/5</p>
				<?php woocommerce_template_single_rating();?>
			</div>
			<div class="reviews-header__right">
				<button class="toggle-review-form">
					Donner mon avis
				</button>
			</div>
		</header>

		<?php if ( have_comments() ) : ?>
			<ol class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper" class="hide">
			<div id="review_form">
				<h3>Donnez votre avis</h3>
				<span class="toggle-review-form">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M1.40002 13.6538L0.346191 12.6L5.94619 7L0.346191 1.4L1.40002 0.346176L7.00002 5.94618L12.6 0.346176L13.6538 1.4L8.05384 7L13.6538 12.6L12.6 13.6538L7.00002 8.05383L1.40002 13.6538Z" fill="#364321"/>
					</svg>
				</span>
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					/* translators: %s is product title */
					'title_reply'         => have_comments() ? esc_html__( 'Donner votre avis', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
					/* translators: %s is product title */
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'    => __( 'Name', 'woocommerce' ),
						'type'     => 'text',
						'value'    => $commenter['comment_author'],
						'required' => $name_email_required,
					),
					'email'  => array(
						'label'    => __( 'Email', 'woocommerce' ),
						'type'     => 'email',
						'value'    => $commenter['comment_author_email'],
						'required' => $name_email_required,
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					/* translators: %s opening and closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] ='<div class="star-wrapper">';

					$comment_form['comment_field'] .= '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
						<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
					</select></div>';
					$comment_form['comment_field'].='
					<p class="form-row form-row-wide">
						<label class="file-drop" for="comment_file">
							<span>Uploader une photo du produit</span>
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<mask id="mask0_189_6712" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
							<rect width="24" height="24" fill="#D9D9D9"/>
							</mask>
							<g mask="url(#mask0_189_6712)">
							<path d="M5.31088 20.5C4.80362 20.5 4.375 20.325 4.025 19.975C3.675 19.625 3.5 19.1964 3.5 18.6891V15H4.99997V18.6923C4.99997 18.7692 5.03202 18.8397 5.09612 18.9039C5.16024 18.968 5.23077 19 5.3077 19H8.99998V20.5H5.31088ZM15 20.5V19H18.6923C18.7692 19 18.8397 18.968 18.9038 18.9039C18.9679 18.8397 19 18.7692 19 18.6923V15H20.5V18.6891C20.5 19.1964 20.325 19.625 19.975 19.975C19.625 20.325 19.1963 20.5 18.6891 20.5H15ZM12 15.5C11.0375 15.5 10.2135 15.1573 9.52813 14.4719C8.84271 13.7864 8.5 12.9625 8.5 12C8.5 11.0375 8.84271 10.2136 9.52813 9.52815C10.2135 8.84274 11.0375 8.50003 12 8.50003C12.9625 8.50003 13.7864 8.84274 14.4718 9.52815C15.1572 10.2136 15.5 11.0375 15.5 12C15.5 12.9625 15.1572 13.7864 14.4718 14.4719C13.7864 15.1573 12.9625 15.5 12 15.5ZM12 14C12.55 14 13.0208 13.8042 13.4125 13.4125C13.8041 13.0208 14 12.55 14 12C14 11.45 13.8041 10.9792 13.4125 10.5875C13.0208 10.1958 12.55 10 12 10C11.45 10 10.9791 10.1958 10.5875 10.5875C10.1958 10.9792 9.99998 11.45 9.99998 12C9.99998 12.55 10.1958 13.0208 10.5875 13.4125C10.9791 13.8042 11.45 14 12 14ZM3.5 9V5.3109C3.5 4.80365 3.675 4.37503 4.025 4.02503C4.375 3.67503 4.80362 3.50003 5.31088 3.50003H8.99998V5H5.3077C5.23077 5 5.16024 5.03205 5.09612 5.09615C5.03202 5.16027 4.99997 5.23079 4.99997 5.30773V9H3.5ZM19 9V5.30773C19 5.23079 18.9679 5.16027 18.9038 5.09615C18.8397 5.03205 18.7692 5 18.6923 5H15V3.50003H18.6891C19.1963 3.50003 19.625 3.67503 19.975 4.02503C20.325 4.37503 20.5 4.80365 20.5 5.3109V9H19Z" fill="#F5F3E6"/>
							</g>
							</svg>

							<input type="file" multiple data-parsley-fileextension="jpg,png" name="comment_file" id="comment_file"  accept=".jpg, .png">

						</label>
					</p>';
					$comment_form['comment_field'].='</div>';

				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			
			</div>
		
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>

	</div>
</div>
