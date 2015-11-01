<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Ensure visibility
if ( ! $product || ! $product->is_visible() )
	return;

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array( 'product-item' );

switch ($woocommerce_loop['columns']) {
	case '2':
		$classes[] = 'df_span-sm-6';
		break;
	case '3':
		if ( $product->is_featured() ) {
		$classes[] = 'df_span-sm-8';
	    } else {
		$classes[] = 'df_span-sm-4';
	    }
		break;
	case '5':
		$classes[] = 'df_span-col5';
		break;	
	default:
	    if ( $product->is_featured() ) {
		$classes[] = 'df_span-sm-6';
	    } else {
		$classes[] = 'df_span-sm-3';
		}
		break;
}
?>
<li <?php post_class( $classes ); ?>>

	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<figure>

	<a>

		
		<?php

			// wc_get_template( 'loop/sale-flash.php' );

			// if ( has_post_thumbnail() ) {
			// 	$image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
			//     $image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
			// 	//echo get_the_post_thumbnail( $post->ID );
			//     echo '<img src=" '. esc_url( $image_link ) .' " title=" '.$image_title.' ">';
			// } elseif ( wc_placeholder_img_src() ) {
			// 	echo wc_placeholder_img();
			// }
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
			
		?>

	</a>	

	<?php
	/*
	 * 	<figcaption class="clearfix">
	 * 		<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
	 * 	 <a title="Quick Look" class="quickview-button" data-id="<?php echo get_the_ID(); ?>"><i class="fa fa-eye"></i></a>
	 *   </figcaption>	
	 */
	?>

	</figure>

	<a>
		<h4><?php the_title(); ?></h4>

		<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
		?>
	</a>	


</li>