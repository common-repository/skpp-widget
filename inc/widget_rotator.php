<?php 

/**
 * Klasa Widgetu
 */
class Skpp_Widget_Rotator extends WP_Widget {

	/* Konstruktor */
	function __construct() {
		parent::__construct(
			'skpp_widget_rotator',
			'Widget SKPP - Rotator produktów',
			array( 'description' => 'Wyświetla rotator produktów' )
		);
	}

	/* glowna funkcja widgetu */
	function widget( $args, $instance ) {

		/* Przed widgetem */
		echo $args['before_widget'];

		$title = apply_filters( 'widget_title' , $instance['title'] );

		if ( $instance['title'] ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( get_option( 'user_products' ) ) {
			$products = skpp_get_user_products();
		} elseif ( $instance['promoted_products'] ) {
			$products = skpp_get_promoted_products();
		}
		else {
			$products = skpp_get_products_for_rotator();
		}

		$products = array_slice($products, 0, 5 );

		echo '<div class="skpp-widget-inner skpp skpp-widget-rotator">';

		foreach ( $products as $product ) {
			echo '<div class="skpp-single-prod">';

				echo '<a rel="nofollow" href="' . skpp_create_link($product->link) . '">';
				if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
					echo '<span class="product_image">';
						echo '<span class="product-image-overlay"></span>';
						echo '<img src="' . skpp_get_image($product->image_linkB) . '" alt="' . htmlspecialchars_decode($product->title) . '" />';  
					echo '</span>';
					echo '<span class="product-inner">';
						echo '<h3>' . htmlspecialchars_decode($product->title) . '</h3>';
						echo '<ul>';
						echo skpp_create_product_description($product->description);
						echo '</ul>';
						echo '<span class="product-bottom-row">';
							if ( 0 != $product->sale_price ) {
								echo '<span class="skpp-price">' . skpp_trim_price($product ->sale_price) . '</span>';
								echo '<span class="skpp-sale-price"><del>' . $product->price . '</del></span>';
							} else {
								echo '<span class="skpp-price">' . skpp_trim_price($product->price) . '</span>';		
							}
							if ( 0 != $product->reviews && 0 != $product->rating ) {
							echo '<span class="skpp-opinion">';
							echo '<span class="skpp-stars">';
							echo '<span class="skpp-stars-inner" style="width:' . skpp_calculate_rating($product->rating) . '%;"></span>';
							echo '</span>';
							echo '<span class="skpp-reviews">' . $product->reviews . '</span>';
							echo '</span>';
						}

						echo '</span>';
					echo '</span>';	
				} else {
					echo '<img src="' . skpp_get_image($product->image_linkA) . '" alt="' . $product->title . '" />'; 
					echo '<span class="product-inner-dvd">';
					if ( $instance['product_name'] ) {
						echo '<h3>' . htmlspecialchars_decode($product->title) . '</h3>';
					} 
					if ( $instance['product_price'] ) {
						echo '<span class="skpp-price">' . skpp_trim_price($product->price) . '</span>';
					}
					echo '</span>';
					echo '<span class="dvd-bottom-spacer"></span>';
				}
				echo '</a>';

			echo '</div>';
		}

		echo '</div>';


		echo $args['after_widget'];
	}

	/* Formularz opcji widgetu */
	function form( $instance ) {
		$defaults = array( 'title' => 'Polecany kurs' );
		$instance = wp_parse_args( (array) $instance, $defaults );

		?>
		
		<!-- Tytul widgetu -->
		<p>
			<label for="title">Tytuł</label><br />
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>"  name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('product_price'); ?>"  name="<?php echo $this->get_field_name('product_price'); ?>" <?php if($instance['product_price'])  echo 'checked="checked"'; ?> />
			<label for="product_price">Pokazać cenę kursu? (Tylko DVD)</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('product_name'); ?>"  name="<?php echo $this->get_field_name('product_name'); ?>" <?php if($instance['product_name'])  echo 'checked="checked"'; ?> />
			<label for="product_name">Pokazać nazwę kursu? (Tylko DVD)</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('promoted_products'); ?>"  name="<?php echo $this->get_field_name('promoted_products'); ?>" <?php if($instance['promoted_products'])  echo 'checked="checked"'; ?> />
			<label for="promoted_products">Tylko produkty na promocji?</label>
		</p>

		<?php

	}

	/* Aktualizacja instancji widgetu */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['product_price'] = strip_tags( $new_instance['product_price'] );
		$instance['product_name'] = strip_tags( $new_instance['product_name'] );
		$instance['promoted_products'] = strip_tags( $new_instance['promoted_products'] );
		return $instance;
	}

}


/**
 * Rejestrujemy widget
 */
function skpp_load_widget_rotator()
{
	register_widget( 'skpp_widget_rotator' );
}
add_action( 'widgets_init' , 'skpp_load_widget_rotator' );