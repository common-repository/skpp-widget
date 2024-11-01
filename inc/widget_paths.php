<?php 

/**
 * Klasa Widgetu
 */
class Skpp_Widget_Paths extends WP_Widget {

	/* Konstruktor */
	function __construct() {
		parent::__construct(
			'skpp_widget_paths',
			'Widget SKPP - Ścieżki kariery',
			array( 'description' => 'Wyświetla pakiety produktów w formie ścieżek kariery.' )
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

		$selected_path = $instance['path'];

		if ( ! $instance['path'] ) {
			$selected_path = 'Specjalista Web designer'; // Domyslna sciezka jestli zadnej nie wybrano 
		}
		
		$path = skpp_get_path_by_name($selected_path);

		$path_image = plugins_url( '/skpp_widget/paths/images/' . $path[2] );

		/* Zawartosc boxa sciezki */
		echo '<a rel="nofollow" href="' . skpp_create_link($path[3]) . '" class="skpp_path">';
			echo '<div class="single-path-inner">';
				echo '<img src="' . $path_image . '" alt="' . $path[0] . '">';
				echo '<div class="single-path-top">';
					echo '<h3>' . $path[0] . '</h3>';
				echo '</div>';
				echo '<div class="single-path-bottom">';
					echo '<ul>';
						echo skpp_create_product_description($path[1]);
					echo '</ul>';
				echo '</div>';
				echo '<div class="single-path-more">Sprawdź szczegóły</div>';
			echo '</div>';
		echo '</a>';

		echo $args['after_widget'];
	}

	/* Formularz opcji widgetu */
	function form( $instance ) {
		$defaults = array( 'title' => 'Ścieżki kariery' );
		$instance = wp_parse_args( (array) $instance, $defaults );

		?>
		
		<!-- Tytul widgetu -->
		<p>
			<label for="title">Tytuł</label><br />
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>"  name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="path">Wybierz ścieżkę</label><br />
			<select type="text" id="<?php echo $this->get_field_id('path'); ?>"  name="<?php echo $this->get_field_name('path'); ?>" value="<?php echo $instance['path']; ?>">
				<?php 
					$paths = skpp_create_paths_list();
					foreach ( $paths as $path ) {
					 	echo '<option value="' . $path . '" ' . selected( $instance['path'], $path, false ) . '>' . $path . '</option>';
					 } 
				?>
			</select>
		</p>
		
		<?php

	}

	/* Aktualizacja instancji widgetu */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['path'] = strip_tags( $new_instance['path'] );
		return $instance;
	}

}


/**
 * Rejestrujemy widget
 */
function skpp_load_widget_paths()
{
	register_widget( 'skpp_widget_paths' );
}
add_action( 'widgets_init' , 'skpp_load_widget_paths' );