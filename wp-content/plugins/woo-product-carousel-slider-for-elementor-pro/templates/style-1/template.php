
<div class="wpce_item">
	<div class="wpce_single_item">
	<?php if( isset($settings['display_image']) && ( $settings['display_image'] == 'yes' ) ){ ?>
		<div class="wpce_thumbnail">
			<a href="<?php echo get_permalink($product->get_id()); ?>">
				<?php
					if( $thumbnail_id ){
						$image_src = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $thumbnail_id, 'thumbnail_size', $settings );
						echo sprintf( '<img src="%s" title="%s" alt="%s"%s />', esc_attr( $image_src ), get_the_title( $thumbnail_id ), wpce_get_attachment_alt($thumbnail_id), '' ); 
					}
				?>
			</a>
		</div>
	<?php } ?>
		<div class="wpce_content">
			
		<?php if( isset($settings['display_title']) && ( $settings['display_title'] == 'yes' ) ){ ?>
			<div class="wpce_title">
				<h2 style="text-align: <?php echo $settings['title_text_align']; ?>" ><a href="<?php echo $product->get_permalink(); ?>"><?php echo $product->get_title(); ?></a></h2>
			</div>
		<?php } ?>

			<div class="wpce_description">

			<?php if( $display_rating == 'yes' ){ ?>
				<div class="wpce-rating">
					<?php

						if ( 'no' !== get_option( 'woocommerce_enable_review_rating' ) ) {
							$rating_count = $product->get_rating_count();
							$review_count = $product->get_review_count();
							$average = $product->get_average_rating();
							$product_id = $product->get_id();

						?>
							<div class="wpce-rating-icons">
								<?php echo wpce_display_product_rating( $average, $rating_count, $product_id ); ?>
							</div>
						<?php
						}
					?>
				</div>
			<?php } ?>
			
			<?php if( $display_price == 'yes' ){ ?>
				<div class="wpce_price">
					<?php echo $product->get_price_html(); ?>
				</div>
			<?php } ?>
			<!-- <?php if( isset($settings['display_content']) && ( $settings['display_content'] == 'yes' ) ){ ?>
				<div class="wpce_text"><?php echo wpautop(wb_get_excerpt()); ?></div>
			<?php } ?> -->
			<?php if( isset($settings['display_read_more']) && ( $settings['display_read_more'] == 'yes' ) ){ ?>
				<div class="wpce_cartbtn">
					<!-- <a class="wpce_add_to_cart_btn" href="<?php the_permalink(); ?>"><?php esc_html_e('read more', 'wpce') ?></a> -->
					<div class="wpce-add-to-cart ">
						<?php
			                echo sprintf( '<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
			                    esc_url( $product->add_to_cart_url() ),
			                    esc_attr( implode( ' ', array_filter( array(
			                        'button', 'product_type_' . $product->get_type(),
			                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
			                        $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			                        'wpce_add_to_cart_btn'
			                    ) ) ) ),
			                    wc_implode_html_attributes( array(
			                        'data-product_id'  => $product->get_id(),
			                        'data-product_sku' => $product->get_sku(),
			                        'aria-label'       => $product->add_to_cart_description(),
			                        'rel'              => 'nofollow',
			                    ) ),
			                    esc_html( $product->add_to_cart_text() )
			                );
			              ?>
	              	</div>
				</div>
			<?php } ?>

			<?php if( isset($settings['display_read_more2']) && ( $settings['display_read_more2'] == 'yes' ) ){ ?>
				<div class="wpce_more" style="text-align: <?php echo $settings['read_more_text_align']; ?>" >
					<a class="wpce_more_btn" href="<?php the_permalink($product->get_id()); ?>"><?php echo $read_more_text; ?></a>
				</div>
			<?php } ?>
			</div>
		
		</div>
	</div>
</div>