<?php

function yoforia_products_sc ($atts, $content = null) {
    global $woocommerce, $woocommerce_loop, $wpdb;
    if (empty($atts)) return;

    extract(shortcode_atts(array(
        "product_type" => "",
        "carousel" => "",
        "widths" => "1/1",
        "item_perpage" => "",
        "product_category" => "",
    ), $atts));

    $args = array();

    /*
     * HANDLE ARRAY ARGUMENTS
     * ------------------------------------------------------------------------
     */
    
    // Latest Products
    if ($product_type == "latest-products") {
        $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'ignore_sticky_posts'   => 1,
                'posts_per_page' => $item_perpage
            );      

    // Featured Products
    } else if ($product_type == "featured-products") {            
        $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'ignore_sticky_posts'   => 1,
                'meta_key' => '_featured',
                'meta_value' => 'yes',
                'posts_per_page' => $item_perpage
            );

    // Products Category
    } else if ($product_type == "product-category") { 
        $categoryQuery = $wpdb->get_results("SELECT wp_posts.ID 
            FROM wp_posts, wp_term_relationships, wp_terms
            WHERE wp_posts.ID = wp_term_relationships.object_id
            AND wp_terms.term_id = wp_term_relationships.term_taxonomy_id
            AND wp_terms.slug = '". $product_category ."'");

        array_walk($categoryQuery, function (&$item, $key) {
            $item = (int) $item->ID;
        });

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page' => $item_perpage,
            'post__in'       => $categoryQuery, 
        );

    // Top Rated
    } else if ($product_type == "top-rated") {
        add_filter( 'posts_clauses',  array( WC()->query, 'order_by_rating_post_clauses' ) );
                
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page' => $item_perpage
        );
        $args['meta_query'] = WC()->query->get_meta_query();
    
    // Recently Viewed
    } else if ($product_type == "recently-viewed") {

        // Get recently viewed product cookies data
        $viewed_products = array();
        if (!empty( $_COOKIE['woocommerce_recently_viewed'])) {
            $viewed_products = (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] );
        }
        $viewed_products = array_filter( array_map( 'absint', $viewed_products ) );
    
        // If no data, quit
        if ( empty( $viewed_products ) ) {
            $returnValue = '<p class="no-products">';
            $returnValue .= __( "You haven't viewed any products yet.", "dahztheme");
            $returnValue .= '</p>';
            return $returnValue;
        }
    
        // Create query arguments array
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page' => $item_perpage, 
            'no_found_rows'  => 1, 
            'post__in'       => $viewed_products, 
            'orderby'        => 'rand'
        );

    // Sale Products
    } else if ($product_type == "sale-products") {

        $product_ids_on_sale = woocommerce_get_product_ids_on_sale();

        $meta_query = array();
        $meta_query[] = WC()->query->visibility_meta_query();
        $meta_query[] = WC()->query->stock_status_meta_query();
        $meta_query   = array_filter( $meta_query );

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page' => $item_perpage,
            'meta_query' => $meta_query,
            'post__in'     => array_merge( array( 0 ), $product_ids_on_sale )
        );

    // Sku Id
    } else if ($product_type == "sku-id") {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts'   => 1,
            //'orderby' => $orderby,
            //'order' => $order,
            'posts_per_page' => $item_perpage,
            'meta_query' => array(
                array(
                    'key'       => '_visibility',
                    'value'     => array('catalog', 'visible'),
                    'compare'   => 'IN'
                )
            )
        );

        if(isset($atts['skus'])){
            $skus = explode(',', $atts['skus']);
            $skus = array_map('trim', $skus);
            $args['meta_query'][] = array(
                'key'       => '_sku',
                'value'     => $skus,
                'compare'   => 'IN'
            );
        }

        if(isset($atts['ids'])){
            $ids = explode(',', $atts['ids']);
            $ids = array_map('trim', $ids);
            $args['post__in'] = $ids;
        }

    // Default
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page' => $item_perpage,
            'meta_key'      => 'total_sales',
            'orderby'       => 'meta_value'
        );
    }
    
    /*
     * HANDLE OUTPUT
     * ------------------------------------------------------------------------
     */
    ob_start();
                 
    $products = new WP_Query( $args );
    $single_layout = get_post_meta( get_the_ID(), 'df_metabox_layout_content', true );
    $number = 4;
    $classes = '';

    // Determine Column Layouts
    if ($single_layout == 'one-col') {
        switch($widths){
            case '2/3':
                $woocommerce_loop['columns'] = 3;
                $number = 3;
                break;
            case '1/2':
                $woocommerce_loop['columns'] = 2;
                $number = 2;
                break;
            case '1/4':
                $woocommerce_loop['columns'] = 1;
                $number = 1;
                break;
            default:
                $woocommerce_loop['columns'] = 4;
            break;
        }

    } elseif ($single_layout == 'two-col-left' || $single_layout == 'two-col-right') {
        $woocommerce_loop['columns'] = 3;
        $number = 3;

    } else {
        switch($widths){
            case '2/3':
                $woocommerce_loop['columns'] = 3;
                $number = 3;
                break;
            case '1/2':
                $woocommerce_loop['columns'] = 2;
                $number = 2;
                break;
            case '1/4':
                $woocommerce_loop['columns'] = 1;
                $number = 1;
                break;
            default:
                $woocommerce_loop['columns'] = 4;
            break;
        }
    }


    $id = mt_rand( 99, 9999 );


    if ( $products->have_posts() ) { ?>
           
        <?php if ($carousel == "yes") { ?>
            <?php wp_enqueue_script('owl-carousel');  ?>

            <script>
            jQuery(function($){
                var owl = $("#carousel-product-<?php echo $id; ?>");

                $(document).ready(function(){
                    owl.owlCarousel({
                        items : <?php echo $number; ?>, 
                        pagination: false,
                        itemsDesktopSmall: [979,3],
                        itemsTablet: [768,2],
                        itemsMobile:   [481,1],
                        afterAction : afterAction
                    });

                    function afterAction (slider_count) {
                        var slider_count = $('#carousel-product-<?php echo $id; ?>')
                            .find('.owl-item').length;

                        if( slider_count < 4 ){
                            $('.next-<?php echo $id ?>').addClass('disabled');
                            $('.prev-<?php echo $id ?>').addClass('disabled');
                        } else {
                            $('.next-<?php echo $id ?>').removeClass('disabled');
                            $('.prev-<?php echo $id ?>').removeClass('disabled');
                        }
                    }

                    if( $(window).width() < 480 ){
                        $('.product-slider-sc li').each(function() {
                            var _this = $(this);
                            _this.css({'width':'auto'});
                        });
                    } else {      
                        $('.product-slider-sc li').each(function() {
                            var _this = $(this);
                            var parentW = $('.owl-item').width() - 30;
                            _this.width(parentW);
                            _this.css({'padding':'0 15px'});
                        });
                    }

                    // Custom Navigation Events
                    $(".next-<?php echo $id ?>").click(function(){
                        owl.trigger('owl.next');
                    })
                    $(".prev-<?php echo $id ?>").click(function(){
                        owl.trigger('owl.prev');
                    })
                });  
            });
            </script>
                 
            <div class="woocommerce product-slider-sc woocommerce-columns-<?php echo $number; ?>">
                <ul id="carousel-product-<?php echo $id; ?>" 
                    class="products df_row-fluid list-<?php echo $product_type; ?>">

                    <?php while ( $products->have_posts() ) : $products->the_post(); ?>
                        <?php wc_get_template_part( 'content', 'product' ); ?>
                    <?php endwhile; // end of the loop. ?>

                </ul>
                <div class="carouselNav control">
                    <a class="next prev-<?php echo $id ?>"><i class="df-arrow-grand-left"></i></a>
                    <a class="prev next-<?php echo $id ?>"><i class="df-arrow-grand-right"></i></a>
                </div>
            </div>
     
        <?php } else {  ?> 
            <div class="woocommerce woocommerce-columns-<?php echo $number; ?>">
                <ul class="products df_row-fluid list-<?php echo $product_type; ?>">
                    <?php while ( $products->have_posts() ) : $products->the_post(); ?>
                        <?php wc_get_template_part( 'content', 'product' ); ?>
                    <?php endwhile; // end of the loop. ?>
                </ul>
            </div>
        <?php } ?>           
    <?php }
           
   $product_list_output = ob_get_contents();
   ob_end_clean();

   /*
    * CleanUp and Return
    */
    wp_reset_query();
    wp_reset_postdata();
    remove_filter( 'posts_clauses',  array( WC()->query, 'order_by_rating_post_clauses' ) );
       
    return $product_list_output;
}

function yoforia_enqueue_carousel_products_sc() {
    global $post;
    if ( has_shortcode( $post->post_content, 'yoforia_products_sc' ) || 
        strpos( $post->post_content, '[yoforia_products_sc' ) ) {
        
        wp_enqueue_style('owl-carousel'); 
    }
}

add_shortcode('yoforia_products_sc', 'yoforia_products_sc');
add_action('df_load_frontend_css', 'yoforia_enqueue_carousel_products_sc');