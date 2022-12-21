<?php
/*
Plugin Name: WooCommerce Automatic CSV Importer
Description: A plugin to automatically import a CSV from a user-defined URL every hour and save it locally as product-import.CSV
Version: 1.0
Author: Steve Coakley
Author URI:
License: GPLv2 or later
Text Domain: woocommerce-csv-importer
*/

function import_csv_from_url() {
    // Get the user-entered CSV URL from the plugin's settings
    $url = get_option( 'csv_importer_csv_url' );
 
    // Retrieve the CSV data from the URL
    $response = wp_remote_get( $url );
 
    // Check if the request was successful
    if ( ! is_wp_error( $response ) ) {
        // Get the body of the response (the CSV data)
        $csv = wp_remote_retrieve_body( $response );
 
        // Save the CSV data to a local file
        $file = fopen( plugin_dir_path( __FILE__ ) . 'product-import.csv', 'w' );
        fwrite( $file, $csv );
        fclose( $file );
    }
}
function schedule_csv_import() {
    // Schedule the import_csv_from_url function to run every hour
    wp_schedule_event( time(), 'hourly', 'import_csv_from_url' );
}

register_activation_hook( __FILE__, 'schedule_csv_import' );

function csv_importer_form() {
    // Get the current value of the CSV URL setting
    $csv_url = get_option( 'csv_importer_csv_url' );
    ?>
    <form method="post" id="csv-importer-form">
        <label for="csv_url">Enter the URL of the CSV file to import:</label>
        <input type="text" name="csv_url" id="csv_url" value="<?php echo esc_attr( $csv_url ); ?>" />
        <input type="submit" value="Import CSV" />
    </form>
    <?php
}
function csv_importer_settings_page() {
    ?>
    <div class="wrap">
        <h1>WooCommerce CSV Importer Settings</h1>
        <form method="post" action="options.php">
            <?php
            // Output nonce, action, and option_page fields for the settings form
            settings_fields( 'csv_importer_settings' );
            // Output the settings sections
            do_settings_sections( 'csv_importer_settings' );
            // Output the submit button
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
function csv_importer_register_settings() {
    // Register the plugin settings
    register_setting( 'csv_importer_settings', 'csv_importer_csv_url', 'esc_url' );
 
    // Add a settings section
    add_settings_section( 'csv_importer_section', 'CSV Import Settings', 'csv_importer_section_callback', 'csv_importer_settings' );
 
    // Add a settings field
    add_settings_field( 'csv_importer_csv_url', 'CSV URL', 'csv_importer_form', 'csv_importer_settings', 'csv_importer_section' );
}
add_action( 'admin_init', 'csv_importer_register_settings' );
function csv_importer_section_callback() {
    // Output a description for the settings section
    echo 'Enter the URL of the CSV file to import';
}

function csv_importer_options_page() {
    // Add a submenu page under the WooCommerce settings menu
    add_submenu_page( 'woocommerce', 'CSV Importer', 'CSV Importer', 'manage_options', 'csv_importer_settings', 'csv_importer_settings_page' );
}
add_action( 'admin_menu', 'csv_importer_options_page' );


