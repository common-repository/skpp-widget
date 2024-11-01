<?php 

/**
 * Klasa Widgetu
 */
class Skpp_Widget extends WP_Widget {

	/* Konstruktor */
	function __construct() {
		parent::__construct(
			'skpp_widget',
			'Widget SKPP - Polecany kurs',
			array( 'description' => 'Wyświetla jeden produkt z oferty wydawnictwa Strefa Kursów' )
		);
	}

	/* glowna funkcja widgetu */
	function widget( $args, $instance ) {

		/* Przed widgetem */
		echo $args['before_widget'];

		$title = apply_filters(  'widget_title' , $instance['title'] );

		if ( $instance['title'] ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( $instance['product_id'] ) {
			$product = skpp_get_product_by_id($instance['product_id']);
		} elseif ( $instance['promoted_products'] ) {
			$product = skpp_get_random_promoted_product();
		}
		 else {
			$product = skpp_get_single_product();
		}

		echo '<div class="skpp-single-prod">';

			echo '<a rel="nofollow" href="' . skpp_create_link($product[1]) . '">';
			if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
				echo '<span class="product_image">';
					echo '<span class="product-image-overlay"></span>';
					echo '<img src="' . skpp_get_image($product[2]) . '" alt="' . $product[0] . '" />';  
				echo '</span>';
				echo '<span class="product-inner">';
					echo '<h3>' . $product[0] . '</h3>';
					echo '<ul>';
					echo skpp_create_product_description($product[4]);
					echo '</ul>';
					echo '<span class="product-bottom-row">';
					if ( 0 != $product[6] ) {
						echo '<span class="skpp-price">' . skpp_trim_price($product[6]) . '</span>';
						echo '<span class="skpp-sale-price"><del>' . $product[3] . '</del></span>';
					} else {
						echo '<span class="skpp-price">' . skpp_trim_price($product[3]) . '</span>';		
					}
						if ( 0 != $product[5] && 0 != $product[7]) {
							echo '<span class="skpp-opinion">';
							echo '<span class="skpp-stars">';
							echo '<span class="skpp-stars-inner" style="width:' . skpp_calculate_rating($product[7]) . '%;"></span>';
							echo '</span>';
							echo '<span class="skpp-reviews">' . $product[5] . '</span>';
							echo '</span>';
						}
					echo '</span>';
				echo '</span>';	
			} else {
				echo '<img src="' . skpp_get_image($product[2]) . '" alt="' . $product[0] . '" />';
				echo '<span class="product-inner-dvd">';
				if ( $instance['product_name'] ) {
					echo '<h3>' . $product[0] . '</h3>';
				} 
				if ( $instance['product_price'] ) {
					echo '<span class="skpp-price">' . skpp_trim_price($product[3]) . '</span>';
				}
				echo '</span>';
				echo '<span class="dvd-bottom-spacer"></span>';
			}
			echo '</a>';

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
		<!-- ID produktu -->
		<p>
			<label for="product_id">ID produktu (opcjonalnie)</label>
			<input type="text" id="<?php echo $this->get_field_id('product_id'); ?>"  name="<?php echo $this->get_field_name('product_id'); ?>" value="<?php echo $instance['product_id']; ?>" />
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
		$instance['product_id'] = strip_tags( $new_instance['product_id'] );
		$instance['promoted_products'] = strip_tags( $new_instance['promoted_products'] );
		return $instance;
	}

}


/**
 * Rejestrujemy widget
 */
function skpp_load_widget()
{
	register_widget( 'skpp_widget' );
}
add_action( 'widgets_init' , 'skpp_load_widget' );