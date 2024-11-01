<?php

/**
 * Pobiera zewnetrzny XML z serwera
 */
function skpp_get_external_xml() {

	$feed_url  = "http://strefakursow.stronazen.pl/feed_strefakursow.xml";
	$skpp_errors = new WP_Error;

	if ( in_array('curl', get_loaded_extensions()) ) {

		$cu = curl_init($feed_url);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cu, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($cu, CURLOPT_CONNECTTIMEOUT, 10);
		$xml = curl_exec($cu);
		$code = curl_getinfo($cu, CURLINFO_HTTP_CODE);

		if ( 200 == $code ) {
			return $xml;
		} else {
			$skpp_errors->add( 'xml',  'Nie można się połączyć z zewnętrznym źródłem danych!' );
			return $skpp_errors;
		}
		curl_close($cu);
		
	} else {
		$skpp_errors->add( 'curl',  'Brak cURL na serwerze! Sprawdz ustawienia PHP albo skontaktuj się z administratorem serwera.' );
		return $skpp_errors;
	}
}

/**
 * Probuje stworzyć glowny katalog cache
 */
function skpp_create_cache_dir() {
	$cache_dir = plugin_dir_path( __DIR__ ) . "skpp_cache";

	if ( ! file_exists($cache_dir) ) {
		if ( ! mkdir($cache_dir, 0775 ) ) {
		} else {
			return true;
		}
	} 
}

/**
 * Probuje stworzyc podkatalogi cache dla obrazkow
 */
function skpp_create_cache_subdirs() {
	$subdirs = array('box','dvd_box');
	$cache_dir = plugin_dir_path( __DIR__ ) . "skpp_cache";

	if ( file_exists( $cache_dir ) ) {
		foreach ( $subdirs as $subdir ) {
			if ( ! file_exists( $cache_dir . "/" . $subdir) ) {
				mkdir( $cache_dir . "/" . $subdir, 0775 );
			} else {
				//echo 'Nie udało się utworzyć podkatalogu' . $cache_dir . '/' . $subdir;
			}
		}
	}	
}

/**
 * Zapisuje XML do cache
 */
function skpp_save_xml_to_cache() {
	$xml_filename = 'feed_strefakursow.xml';
	$cache_dir = plugin_dir_path(__DIR__ ) . "skpp_cache";
	$file = $cache_dir . '/' . $xml_filename;
	$src = "http://strefakursow.pl/plugin/comparer/partner_program/products/158307ef32.xml";

	if ( is_writable( $cache_dir ) ) {

		$cu = curl_init($src);
		$file_open = fopen( $file, 'w' );
		curl_setopt($cu, CURLOPT_FILE, $file_open);
		curl_setopt($cu, CURLOPT_HEADER, 0);
		curl_exec($cu);
		curl_close($cu);
		fclose($file_open);
		return true;
	} else {
		return false;
	}
}


/**
 * Pobiera XML z local albo external url 
 */
function skpp_get_source() {
	if ( file_exists( plugin_dir_path( __DIR__ ) . "skpp_cache/feed_strefakursow.xml" ) ) {
		$source = plugin_dir_url( __DIR__ ) . "skpp_cache/feed_strefakursow.xml";
	} else {
		$source = "http://strefakursow.pl/plugin/comparer/partner_program/products/158307ef32.xml";
	}
	return $source;
}

/**
 * Aktualizuje XML w cache
 */
function skpp_update_xml() {
	$xml = skpp_get_external_xml();
	$file_updated = skpp_save_xml_to_cache( $xml );
	if ( false == $file_updated ) {
		echo 'Błąd aktualizacji XML';
		return false;
	} else {
		return true;
	}
}

/**
 * Generuje nazwe pliku obrazka
 * @param string  $image_link link do obrazka
 * @return string $filename nazwa obrazka
 */
function skpp_generate_image_filename( $image_link ) {
	$link_string = preg_split('#\/shop\/\w+\/#', $image_link);
	$filename = $link_string[1];
	return $filename;
}

/**
 * Pobiera obrazek, zapisuje do cache i zwraca jego adres
 * @param string $image_filename link do obrazka
 * @return link do obrazka z cache albo serwera
 */
function skpp_get_image( $image_filename ) {
	$product_style = get_option( 'skpp_product_style' );
	$filename = skpp_generate_image_filename( $image_filename );

	if ( 'skpp_box' == $product_style ) {
		$style = 'box';
	} else {
		$style = 'dvd_box';
	}

	if ( file_exists( plugin_dir_path( __DIR__ ) . "skpp_cache/$style/$filename" ) ) {
		$src = plugin_dir_url(  __DIR__ ) . "skpp_cache/$style/$filename";
	} else {
		$src = "http://strefafilmy.s3.amazonaws.com/product_picture/shop/$style/$filename";
		
		if ( ! is_dir( plugin_dir_path( __DIR__ ) . "skpp_cache/$style" ) ) {
			mkdir( plugin_dir_path( __DIR__ ) . "skpp_cache/$style/", 0775, true );
		}

		$cu = curl_init($src);
		$file_open = fopen( plugin_dir_path( __DIR__ ) . "skpp_cache/$style/$filename", 'w' );
		curl_setopt($cu, CURLOPT_FILE, $file_open);
		curl_setopt($cu, CURLOPT_HEADER, 0);
		curl_exec($cu);
		curl_close($cu);
		fclose($file_open);
	}

	return $src;
}