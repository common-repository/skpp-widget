<?php
/*
Plugin Name: SKPP - Widget
Plugin URI: http://strefakursow.pl/program_partnerski.html
Description: Widgety oraz shortcode Programu Partnerskiego Strefa Kursów
Version: 1.1.5
Author: Strefa Kursów
License: GPLv2 or later
*/

include_once('inc/functions.php');
include_once('inc/menu.php');
include_once('inc/widget.php');
include_once('inc/widget_rotator.php');
include_once('inc/widget_paths.php');
include_once('inc/shortcodes.php');
include_once('inc/cache.php');

/**
 * Aktywacja
 */
register_activation_hook( __FILE__, 'skpp_activate' );

function skpp_activate() {
	/* Przygotowuje aktualizacje XML raz na dzien */
	$xml_update = wp_next_scheduled( 'skpp_update_data' );
	if ( false == $xml_update ) {
		wp_schedule_event( time(), 'daily', 'skpp_update_data' );
	}
	skpp_create_cache_dir();
	skpp_create_cache_subdirs();

	/* Ustawia domyslne opcje stylu */
	update_option( 'skpp_product_style', 'skpp_box' );
	update_option( 'skpp_text_color', '#3A3F48' );
	update_option( 'skpp_bg_color', '#FFFFFF' );
}

//add_filter ( 'cron_schedules' , 'skpp_weekly_schedule' );
add_action( 'skpp_update_data', 'skpp_update_xml' );


/**
 * Deaktywacja
 */
register_deactivation_hook( __FILE__, 'skpp_deactivate' );

function skpp_deactivate() {
	wp_clear_scheduled_hook( 'skpp_update_data' );
}

/**
 * Deinstalacja
 */
register_uninstall_hook(__FILE__, "skpp_uninstall");

function skpp_uninstall() {

	/* Czysci pliki */
	 function skpp_rrmdir($dir) {
	  	if (is_dir($dir)) {
	    	$objects = scandir($dir);
	    	foreach ($objects as $object) {
		      	if ($object != "." && $object != "..") {
		        	if (filetype($dir."/".$object) == "dir") 
		           		skpp_rrmdir($dir."/".$object); 
		        	else unlink($dir."/".$object);
		      		}
		    	}
	    	reset($objects);
	    	rmdir($dir);
	  	}
 	}

	/* Usuwa cache */
	$cache_dir = plugin_dir_path( __DIR__ ) . "/skpp_cache";
	skpp_rrmdir($cache_dir);

	/* Usuwamy opcje */
	delete_option( 'skpp_speed' );
	delete_option( 'skpp_product_style' );
	delete_option( 'skpp_bg_color' );
	delete_option( 'skpp_text_color' );
	delete_option( 'skpp_partner_id' );

}


