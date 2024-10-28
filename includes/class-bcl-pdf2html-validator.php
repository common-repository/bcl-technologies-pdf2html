<?php

class BCL_PDF2HTML_Validator 
{
    function __construct() {}

    public function validate() {
        $is_valid = $this->is_php_version_ok() && $this->is_wp_version_ok() && $this->is_upload_limit_ok() && $this->is_uploads_dir_ok();
        if( ! $is_valid ) {
            add_action( 'admin_init' , array( $this, 'bcl_disable_plugin' ) );
            add_action( 'admin_notices' , array( $this, 'bcl_admin_notice' ) );
            return false;
        }

        return true;
    }

    public function bcl_disable_plugin() 
    {   
        if( current_user_can( 'activate_plugins' ) && is_plugin_active( 'bcl-pdf2html/bcl-pdf2html.php' ) ) {
            
            deactivate_plugins( 'bcl-pdf2html/bcl-pdf2html.php' );

            // Hide the default "Plugin activated" notice
            if( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }
    }

    // https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
    public function bcl_admin_notice() {
        $class = 'notice notice-error is-dismissible';
        $message = 'BCL PDF2HTML - Oops! It looks like your system does not meet the requirements to use this plugin. 
                Please check the documentation and enable the appropriate settings. 
                <br>
                <strong>Too much work?</strong> Try us for free at 
                <a href="http://www.pdfonline.com" target="_blank">http://www.pdfonline.com</a>.';
        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }

    private function is_php_version_ok() {
        if( version_compare( PHP_VERSION, '5.2', '<' ) ) {
            return false;
        }

        return true;
    }

    private function is_wp_version_ok() {
        global $wp_version;

        if( version_compare( $wp_version, '4.4', '<' ) ) {
            return false;
        }

        return true;
    }

    private function is_upload_limit_ok() {
        return wp_max_upload_size() > 0;
    }

    private function is_uploads_dir_ok() {
        $uploads = wp_upload_dir();
        if( ! empty( $uploads["error"] ) ) {
            // Directory may not be writable.
            return false;
        }

        return true;
    }
}

?>