<?php

class BCL_PDF2HTML_Upload
{
	private $returned_html;
	private $BCL_PDF2HTML_HOOK;
	private $UPLOADS_DIR_INFO;
	private $CONVERSION_SERVICE_URL = "http://207.135.71.170:8080/wpupload/v1";

    function __construct() {
    	add_action( 'admin_menu', array( $this, 'bcl_pdf2html_admin_menu' ) );
    	add_action( 'admin_enqueue_scripts', array( $this, 'bcl_load_externals' ) );

    	$this->UPLOADS_DIR_INFO = wp_upload_dir();
    }

    function bcl_pdf2html_admin_menu() {
    	$this->BCL_PDF2HTML_HOOK = add_menu_page( 
								'BCL PDF2HTML', // the title tag for the page, e.g. <title>BCL PDF2HTML</title>
								'BCL PDF2HTML', // the title of the admin menu
								'manage_options', // access level
								'bcl-technologies-pdf2html', // URL slug
								array( $this, 'bcl_upload_form' ), // function that generates the page
								'dashicons-media-code', // using a WP provided icon (https://developer.wordpress.org/resource/dashicons/#media-code)
								6 // item location (possibly change this)
							);
    }

    // Only load these for this particular plugin.
    function bcl_load_externals( $hook ) {
    	if( $hook != $this->BCL_PDF2HTML_HOOK )
	    	return;

	    // load css
	    wp_enqueue_style( 'jquery-ui-dialog-css', includes_url( 'css/jquery-ui-dialog.css' ) );
    	wp_enqueue_style( 'bclec5-css', plugins_url( 'css/bclec5.css' , dirname( __FILE__ ) ) );

    	// load js
    	$deps = array( 'jquery', 'jquery-ui-dialog' );
    	wp_enqueue_script( 'bclec5-js', plugins_url( 'js/bclec5.js' , dirname( __FILE__ ) ), $deps, null, true );
    }

    function bcl_upload_form() {
        ?>

        <h1 class="bcl-header">BCL Convert PDF to HTML</h1>

        <form class="bcl-temp" id="bcl-upload-form" action="" method="post" enctype="multipart/form-data">
        
			<div style="margin-bottom: 10px; font-size: 14px;">
				<input type="radio" id="r1" name="inline" value="false" checked="checked">
			  	<label for="r1">Full HTML file (recommended)</label>
			</div>

		  	<input type="radio" id="r2" name="inline" value="true">
		  	<label for="r2">Inline CSS (Note: the resulting HTML may be affected by the current theme's styles)</label>

		  	<br><br>
			
	        <input type='file' id='wp-upload' name='wp-upload'>
	        <input type="submit" value="Upload PDF">
			
			<p><i>(10MB limit)</i></p>
	    </form>

        <br>

        <div id="loading-screen" title="Processing...">
            <img src=<?php echo '"' . plugins_url( 'images/loading.gif' , dirname( __FILE__ ) ) . '"' ?> id="loading-gif" alt="loading" width="40" height="40" />
            <br/>
            <p align="center">
                <b>Please wait.</b> Documents with lots of images and tables may take a while to convert.
            </p>
        </div>

        <?php

        $this->process();
    }

    private function process() {
    	if( isset( $_FILES['wp-upload'] ) ) {
			$upload_error_code = $_FILES['wp-upload']['error'];

			if( $upload_error_code > 0 ) {
				echo "<h1 class='bcl-temp'>ERROR UPLOADING DOCUMENT. Error Code: " . $upload_error_code . "</h1>";
			} elseif ( $this->file_ext( $_FILES['wp-upload']['name'] ) != "pdf" ) {
				echo "<p class='bcl-temp'>Oops! Your document must be a PDF.</p>";
			} else {
				$this->upload_to_conversion_service();

				$this->publish();
			}
		}
    }

    private function file_ext( $filename ) {
    	$info = wp_check_filetype( $filename );
    	return $info['ext'];
    }

    private function publish() {
    	if( ! empty( $this->returned_html ) ) {
			if( $_POST['inline'] == 'false' ) {
				$current_upload_info = $this->save_to_local( $this->returned_html );
				if( ! empty( $current_upload_info ) ) {
					$uploaded_file_url = $this->move_to_bcl_directory( $current_upload_info );
					$this->create_page( $uploaded_file_url );
				}

			} else {
				$this->creat_page_with_inline_css( $this->returned_html );
			}
		}
    }

	private function upload_to_conversion_service() {
		$local_file = $_FILES['wp-upload']['tmp_name'];
		
		// Include user inline option in the request.
		$post_fields = array (
			'inline' => $_POST['inline']
		);

		$boundary = wp_generate_password( 24 );
		$headers  = array(
			'content-type' => 'multipart/form-data; boundary=' . $boundary
		);
		$payload = '';

		// Add the standard POST fields:
		foreach ( $post_fields as $name => $value ) {
			$payload .= '--' . $boundary;
			$payload .= PHP_EOL;
			$payload .= 'Content-Disposition: form-data; name="' . $name . '"' . PHP_EOL . PHP_EOL;
			$payload .= $value;
			$payload .= PHP_EOL;
		}

		// Add the file
		if ( $local_file ) {
			$payload .= '--' . $boundary;
			$payload .= PHP_EOL;
			$payload .= 'Content-Disposition: form-data; name="' . 'wp-upload' . '"; filename="' . $_FILES['wp-upload']['name'] . '"' . PHP_EOL;
			$payload .= 'Content-Type: ' . $_FILES['wp-upload']['type'] . PHP_EOL;
			$payload .= PHP_EOL;
			$payload .= file_get_contents( $local_file );
			$payload .= PHP_EOL;
		}

		$payload .= '--' . $boundary . '--';
		$response = wp_remote_post( $this->CONVERSION_SERVICE_URL,
			array(
			'headers' => $headers,
			'timeout' => 130, // 2 min 10 secs
			'body'    => $payload
			)
		);

		// Adding custom class 'bcl-temp' so we can hide these messages on the next upload.
		if( is_wp_error( $response ) ) 
		{
			$error_message = $response->get_error_message();
			echo "<p class='bcl-temp'>Oops! Something went wrong: $error_message</p>";
		} elseif ( $response['response']['code'] == 200 ) {
			// Set the returned contents to the field returned_html
			$this->returned_html = $response['body'];
		} else {
			$tmp_message = $response['body'];
			echo "<p class='bcl-temp'>We are unable to convert your document: $tmp_message</p>";
		}
	}

	private function save_to_local( $file_data ) {
		$file_name = "bcl_" . $_SERVER['REQUEST_TIME'] . ".htm";
		$upload_info = wp_upload_bits( $file_name, null, $file_data );
		if( ! empty( $upload_info['error'] ) ) {
			echo "<p class='bcl-temp'>Error calling WordPress function wp_upload_bits. Unable to save your document to the Uploads folder.</p>";
			return null;
		} else {
			return $upload_info;
		}
	}

	private function move_to_bcl_directory( $current_upload_info ) {
		$current_file_path = $current_upload_info['file'];
		$path_parts = pathinfo( $current_file_path );
		$file_name = $path_parts['basename'];
		$current_dir = $path_parts['dirname'];


		$bcl_dir = $this->UPLOADS_DIR_INFO['basedir'] . '/' . 'easyConverter5';
		if( wp_mkdir_p( $bcl_dir ) ) {
			$this->UPLOADS_DIR_INFO['basedir'] = $bcl_dir;
		} else {
			$this->UPLOADS_DIR_INFO['basedir'] = $current_dir;
		}
		
		$updated_file_path = $this->UPLOADS_DIR_INFO['basedir'] . '/' . $file_name;

		if( ($current_file_path != $updated_file_path) && rename( $current_file_path, $updated_file_path ) ) {
			$this->UPLOADS_DIR_INFO['baseurl'] .= '/' . 'easyConverter5' . '/' . $file_name;
		} else {
			$this->UPLOADS_DIR_INFO['baseurl'] = $current_upload_info['url'];
		}

		return $this->UPLOADS_DIR_INFO['baseurl'];
	}

	private function create_page( $updated_file_url ) {
		$description = sprintf( 'This document was converted using easyConverter 5 HTML by BCL Technologies. 
			View it <a href="%s">here.</a>', $updated_file_url );

		$post_contents = array(
			'post_title'   => $_FILES['wp-upload']['name'],
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => 'page'
		);

		$success = wp_insert_post( $post_contents );
		if( ! $success ) {
			// Direct link to URL in uploads folder
			printf( '<p class="bcl-temp">View your converted document <a href="%s">here.</a></p>', $updated_file_url );
		} else {
			printf( '<p class="bcl-temp">Successfully published a page! View it <a href="%s">here.</a></p>', get_page_link( $success ) );
		}
	}

	private function creat_page_with_inline_css( $html_str ) {
		$post_contents = array(
			'post_title' => $_FILES['wp-upload']['name'] . ' (with inline CSS)',
			'post_content' => $html_str,
			'post_status' => 'publish',
			'post_type' => 'page'
		);

		$success = wp_insert_post( $post_contents );
		if( ! $success ) {
			echo "<p class='bcl-temp'>We were unable to publish a page with your document contents. Please try again.</p>";
		} else {
			printf( '<p class="bcl-temp">Successfully published a page! View it <a href="%s">here.</a></p>', get_page_link($success) );
		}
	}
}

?>