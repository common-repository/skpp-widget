<?php 
/**
 * Podpinamy style
 */
function skpp_load_styles() {
 	wp_enqueue_style( 'skpp_style' ,  plugins_url( 'skpp-widget/addons/style.css' ) );
 	wp_enqueue_style( 'skpp-fonts' , 'https://fonts.googleapis.com/css?family=Open+Sans:400,600&subset=latin,latin-ext' );
 	$bgcolor = wp_strip_all_tags( get_option( 'skpp_bg_color' ) );
 	$textcolor = wp_strip_all_tags( get_option( 'skpp_text_color' ) );
 	$user_css = "
 		.skpp-single-prod {
 			background: {$bgcolor};
 		}
 		.skpp-single-prod a h3, .skpp-single-prod a ul li {
 			color: {$textcolor};
 		}";
 	wp_add_inline_style( 'skpp_style', $user_css );
}
add_action( 'wp_enqueue_scripts', 'skpp_load_styles' );
add_action( 'admin_head', 'skpp_load_styles' );

/**
 * Podpinamy skrypty
 */
function skpp_load_scripts() {
	wp_enqueue_script( 'skpp-showoff' , plugins_url( '/skpp_widget/addons/jquery.jshowoff.js' ), array('jquery') );
	wp_enqueue_script( 'skpp-custom' , plugins_url( '/skpp_widget/addons/skpp_custom.js' ), array('jquery') );
	if ( ! get_option( 'skpp_speed' ) ) {
		$speed = 3000;
	} else {
		$speed = get_option( 'skpp_speed' );
	}
	$showoff_params = array( 'speed' => $speed );
	wp_localize_script( 'skpp-custom', 'showOffParams', $showoff_params );
}
add_action( 'wp_enqueue_scripts' , 'skpp_load_scripts' );


/**
 * Pobiera dane z XML (NOWA)
 */
function skpp_get_feed() {
	$feed_url = skpp_get_source();
	$skpp_errors = new WP_Error;

	if ( in_array('curl', get_loaded_extensions()) ) {

		$cu = curl_init($feed_url);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cu, CURLOPT_CONNECTTIMEOUT, 10);
		$xml = curl_exec($cu);
		$code = curl_getinfo($cu, CURLINFO_HTTP_CODE);

		if ( 200 == $code ) {

			if ( ! file_exists( plugin_dir_path( __DIR__  ) . "skpp_cache/feed_strefakursow.xml")  ) {
				skpp_save_xml_to_cache($xml); // Zapisujemy do pliku lokalnie
			}
			$xml = simplexml_load_string($xml);
			return $xml;

		} else {
			$skpp_errors->add( 'xml',  'Błąd XML - nie można wczytać danych.' );
			if ( ! is_admin() ) { // Komunikat tylko na front
				echo 'Błąd XML - nie można wczytać danych.';
			}
			return $skpp_errors;
		}

		curl_close($cu);

	} else {
		$skpp_errors->add( 'curl',  'Brak cURL na serwerze! Sprawdz ustawienia PHP albo skontaktuj się z administratorem serwera.' );
		if ( ! is_admin() ) { // Komunikat tylko na front
			echo 'Brak cURL na serwerze!';
		}
		return $skpp_errors;
	}
}

/**
 * Zlicza produkty
 */
function skpp_count_products() {
	if ( ! get_option( 'user_products' ) ) {
		$products = skpp_get_feed()->channel;
		$count = $products->children()->count()-2;
	} else {
		$products = skpp_get_user_products();
		$count = count($products);
	}
	return $count;
}

/**
 * Wybiera losowa wartosc
 */
function skpp_select_random_product() {
	if ( ! get_option( 'user_products' ) ) {
		$rand = rand(0, skpp_count_products()-1); // Odejmuje zeby zmiescic sie w zakresie
	} else {
		$rand = rand(0, skpp_count_products()-1); // Odejmuje zeby zmiescic sie w zakresie 
	}
	return $rand;
}

/**
 * Pobiera jeden produkt
 */
function skpp_get_single_product() {
	if ( ! get_option( 'user_products' ) ) {
		$products = skpp_get_feed()->channel;
		$product = $products->item[skpp_select_random_product()];
	} else {
		$products = skpp_get_user_products();
		$product = $products[skpp_select_random_product()];
	}
	$product_name = htmlspecialchars_decode($product->title);
	$product_link = $product->link;
	if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
		$product_image = $product->image_linkB; // box
		$product_features = $product->description;
		$product_reviews = $product->reviews;
		$product_rating = $product->rating;		
	} else {
		$product_image = $product->image_linkA; // dvd
		$product_features = "";
		$product_reviews = "";
		$product_rating = "";
	}
	$product_price = $product->price;
	if ( ! empty($product->sale_price) ) {
		$product_sale_price = $product->sale_price;
	} else {
		$product_sale_price = '';
	}
	return array($product_name, $product_link, $product_image, $product_price, $product_features, $product_reviews, $product_sale_price, $product_rating);
}

/**
 * Pobiera produkt z okreslonym ID
 * @param integer id identyfikator produktu 
 */
function skpp_get_product_by_id( $id ) {
	$products = @skpp_get_feed()->channel;
	$product_string = 'item[id="' .  $id . '"]';
	$product = @$products->xpath($product_string);
	$product = $product[0];
	$product_name = htmlspecialchars_decode($product->title);
	$product_link = $product->link;
	if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
		$product_image = $product->image_linkB; // box
		$product_features = $product->description;
		$product_reviews = $product->reviews;
		$product_rating = $product->rating;
		
	} else {
		$product_image = $product->image_linkA; // dvd
		$product_features = "";
		$product_reviews = "";
		$product_rating = "";
	}
	$product_price = $product->price;
	if ( ! empty($product->sale_price) ) {
		$product_sale_price = $product->sale_price;
	} else {
		$product_sale_price = '';
	}
	return array($product_name, $product_link, $product_image, $product_price, $product_features, $product_reviews, $product_sale_price, $product_rating);
}

/**
 * Zwraca wszystkie produkty
 */
function skpp_get_all_products() {
	$products = @skpp_get_feed()->channel->children();
	unset($products[0]);
	unset($products[0]);
	return $products;
}

/**
 * Zwraca produkty uzytkownika
 */
function skpp_get_user_products() {
	$products_list = get_option( 'user_products' );
	$products = skpp_get_all_products();
	$selected_products = array();
	foreach ( $products as $product ) {
		if ( $product->id == in_array($product->id, $products_list) ) {
			$selected_products[] = $product;
		}
	}
	return $selected_products;
}

/**
 * Zwraca wszystkie kategorie
 */
function skpp_get_all_categories() {
	$products = @skpp_get_feed()->channel;
	$categories = @$products->xpath('item/product_type');
	$categories = array_unique($categories);
	return $categories;
}

/**
 * Zwraca produkty z kategorii
 */
function skpp_get_products_by_category( $category = 'Fotografia' ) {
	$products = @skpp_get_feed()->channel;
	$category_string = 'item[product_type="' . $category . '"]';
	$products_list = @$products->xpath($category_string);
	if ( "" == $category ) {
		$products_list = skpp_get_all_products();
	}
	return $products_list;
}

/**
 * Zwraca produkty promowane (wszystkie)
 */
function skpp_get_promoted_products() {
	$products = skpp_get_all_products();
	$promoted_products = array();
	foreach ( $products as $product ) {
		if ( isset( $product->sale_price ) ) {
			$promoted_products[] = $product;	
		}	
	}
	return $promoted_products;	
}

/**
 * Zwraca jeden promowany produkt (losowo)
 */
function skpp_get_random_promoted_product() {
	$products = skpp_get_promoted_products();
	shuffle( $products );
	$product = $products[0];
	$product_name = htmlspecialchars_decode($product->title);
	$product_link = $product->link;
	if ( 'skpp_box' == get_option( 'skpp_product_style' ) ) {
		$product_image = $product->image_linkB; // box
		$product_features = $product->description;
		$product_reviews = $product->reviews;
		
	} else {
		$product_image = $product->image_linkA; // dvd
		$product_features = "";
		$product_reviews = "";
	}
	$product_price = $product->price;
	$product_sale_price = $product->sale_price;
	return array($product_name, $product_link, $product_image, $product_price, $product_features, $product_reviews, $product_sale_price);	
}

/**
* Generuje produkty dla rotatora
*/
function skpp_get_products_for_rotator() {
	$products = skpp_get_all_products();
	$rotator_products = array();
	foreach ( $products as $product ) {
		$rotator_products[] = $product;
	}
	shuffle( $rotator_products );
	return $rotator_products;
}

/**
 * Generuje link partnerski
 */
function skpp_create_link( $link ) {
	$link = $link . '?ref=' . get_option( 'skpp_partner_id' );
	return $link;
}

/**
 * Generuje opis produktu (oraz ficzery dla sciezek)
 */
function skpp_create_product_description( $data ) {
	$features = explode(";", $data);
	$list = "";
	foreach ( $features as $feature ) {
		$list .= '<li>' . $feature . '</li>';
	}
	return $list;
}

/**
 * Zwraca obcieta cene
 */
function skpp_trim_price( $price ) {
	$trim_price = str_replace(".00", "", $price);
	return $trim_price;
}

/**
 * Zwraca oceny dla gwiazdek
 */
function skpp_calculate_rating( $rating ) {
	$final_rating = (float)$rating*20; // Tyle trzeba dla prawidlowej szerokosci span gwiazdek
	return $final_rating;
}

/**
 * SCIEZKI - pobiera zawartosc z XML
 */
function skpp_get_paths_data() {
	$file = plugins_url( '/skpp_widget/paths/skpp_paths.xml' );
	$cu = curl_init($file);
	curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
	$xml = curl_exec($cu);
	$xml = simplexml_load_string($xml);
	curl_close($cu);	
	return $xml;
}

/**
 * SCIEZKI - Generuje liste sciezek
 */
function skpp_create_paths_list() {
	$paths = skpp_get_paths_data()->children();
	$paths_list = array();
	foreach ( $paths as $path ) {
		$paths_list[] = $path->name;
	}
	return $paths_list;
}

/**
* SCIEZKI - zwraca jedna sciezke
*/
function skpp_get_path_by_name( $name ) {
	$paths = skpp_get_paths_data();
	$name_string = 'path[name="' . $name . '"]'; 
	$path = $paths->xpath($name_string);
	$path = $path[0];

	$path_name = $path->name;
	$path_features = $path->features;
	$path_img = $path->image;
	$path_link = $path->path_link;

	return array($path_name, $path_features, $path_img, $path_link);
}
