<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Require composer autoload for psr-4
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use Afca\Themes\TheRateFramework\Init;
$theme = wp_get_theme();
new Init( get_template_directory(), get_template_directory_uri(), $theme->get('Version') );
