<?php

if( !defined( 'ABSPATH' ) )
    exit;

/**
 * Plugin Name: BCL Technologies PDF2HTML
 * Plugin URI: http://www.pdfonline.com
 * Description: Upload a PDF document and convert it to HTML.
 * Version: 1.0.0
 * Author: BCL Technologies
 * Author URI: http://www.pdfonline.com/
 * License: GPL2
 */

require_once plugin_dir_path( __FILE__ ) . 'includes/class-bcl-pdf2html-validator.php';

$bcl_pdf2html_validator = new BCL_PDF2HTML_Validator();

if( $bcl_pdf2html_validator->validate() ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bcl-pdf2html-upload.php';
	new BCL_PDF2HTML_Upload();
}

?>