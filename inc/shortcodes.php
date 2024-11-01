<?php 

function skpp_product_callback( $atts ) {

	$args = shortcode_atts(array(
		'id' => ''
		),$atts);

	$id = esc_attr( $args['id'] );
	$product = skpp_get_product_by_id( $id );

	$output = '<div class="skpp-single-prod skpp-product-shortcode">';

			$output .= '<a rel="nofollow" href="' . skpp_create_link($product[1]) . '">';
			if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
				$output .= '<span class="product_image">';
					$output .= '<span class="product-image-overlay"></span>';
					$output .= '<img src="' . skpp_get_image($product[2]) . '" alt="' . htmlspecialchars_decode($product[0]) . '" />';  
				$output .= '</span>';
				$output .= '<span class="product-inner">';
					$output .= '<h3>' . htmlspecialchars_decode($product[0]) . '</h3>';
					$output .= '<ul>';
					$output .= skpp_create_product_description($product[4]);
					$output .= '</ul>';
					$output .= '<span class="product-bottom-row">';
						$output .= '<span class="skpp-price">' . skpp_trim_price($product[3]) . '</span>';
						$output .= '<span class="skpp-sale-price"><del>' . $product[6] . '</del></span>';
						if ( 0 != $product[5] && 0 != $product[7]) {
							$output .= '<span class="skpp-opinion">';
							$output .= '<span class="skpp-stars">';
							$output .= '<span class="skpp-stars-inner" style="width:' . skpp_calculate_rating($product[7]) . '%;"></span>';
							$output .= '</span>';
							$output .= '<span class="skpp-reviews">' . $product[5] . '</span>';
							$output .= '</span>';
						}

					$output .= '</span>';
				$output .= '</span>';	
			} else {
				$output .= '<img src="' . skpp_get_image($product[2]) . '" alt="' . $product[0] . '" />'; 
				$output .= '<span class="product-inner-dvd">';
					$output .= '<h3>' . htmlspecialchars_decode($product[0]) . '</h3>';
					$output .= '<span class="skpp-price">' . skpp_trim_price($product[3]) . '</span>';
				$output .= '</span>';
				$output .= '<span class="dvd-bottom-spacer"></span>';
			}
			$output .= '</a>';

		$output .= '</div>';

	return $output;
}

function skpp_catalog_callback( $atts ) {
	$args = shortcode_atts(array(
		'category' => '',
		'number' => '3',
		'price' => '',
		'title' => ''
		),$atts);

	$category = esc_attr( $args['category'] );
	$i = esc_attr( $args['number'] );
	$price = esc_attr( $args['price'] );
	$title = esc_attr( $args['title'] );
	$products = skpp_get_products_by_category( $category );
	$products = array_reverse($products); // Najnowsze produkty u gory

	$output = '<div class="skpp-catalog-container">';
	foreach ( array_slice( $products, 0, $i ) as $product ) {
		$output .= '<div class="skpp-single-prod-col">';
		$output .= '<div class="skpp-single-prod">';

			$output .= '<a rel="nofollow" href="' . skpp_create_link($product->link) . '">';
			if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
				$output .= '<span class="product_image">';
					$output .= '<span class="product-image-overlay"></span>';
					$output .= '<img src="' . skpp_get_image($product->image_linkB) . '" alt="' . htmlspecialchars_decode($product->title) . '" />';  
				$output .= '</span>';
				$output .= '<span class="product-inner">';
					$output .= '<h3>' . htmlspecialchars_decode($product->title) . '</h3>';
					$output .= '<ul>';
					$output .= skpp_create_product_description($product->description);
					$output .= '</ul>';
					$output .= '<span class="product-bottom-row">';
						$output .= '<span class="skpp-price">' . skpp_trim_price($product->price) . '</span>';
						$output .= '<span class="skpp-sale-price"><del>' . $product->sale_price . '</del></span>';
						if ( 0 != $product->reviews && 0 != $product->rating ) {
							$output .= '<span class="skpp-opinion">';
							$output .= '<span class="skpp-stars">';
							$output .= '<span class="skpp-stars-inner" style="width:' . skpp_calculate_rating($product->rating) . '%;"></span>';
							$output .= '</span>';
							$output .= '<span class="skpp-reviews">' . $product->reviews . '</span>';
							$output .= '</span>';
						}

					$output .= '</span>';
				$output .= '</span>';	
			} else {
				$output .= '<img src="' . skpp_get_image($product->image_linkA) . '" alt="' . $product->title . '" />'; 
				$output .= '<span class="product-inner-dvd">';
				if ( 'true' == $title ) {
					$output .= '<h3>' . htmlspecialchars_decode($product->title) . '</h3>';
				}
				if ( 'true' == $price ) {
					$output .= '<span class="skpp-price">' . skpp_trim_price($product->price) . '</span>';
				}
				$output .= '</span>';
				$output .= '<span class="dvd-bottom-spacer"></span>';
			}
			$output .= '</a>';

		$output .= '</div>';
		$output .= '</div>';
	}
	$output .= '</div>';
	return $output;
}

add_shortcode( 'skpp_product', 'skpp_product_callback' );
add_shortcode( 'skpp_catalog', 'skpp_catalog_callback' );