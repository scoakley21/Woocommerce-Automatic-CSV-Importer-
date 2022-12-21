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
    // Check if the form has been submitted
    if ( isset( $_POST['csv_url'] ) ) {
        // Get the user-entered CSV URL
        $url = esc_url( $_POST['csv_url'] );
 
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
 
            // Display a success message
            echo '<p>CSV successfully imported!</p>';
 
            // Display the URL of the CSV file
            $csv_url = plugins_url( 'product-import.csv', __FILE__ );
            echo '<p>CSV URL: ' . $csv_url . '</p>';
        }
    }
}

function schedule_csv_import() {
    // Schedule the import_csv_from_url function to run every hour
    wp_schedule_event( time(), 'hourly', 'import_csv_from_url' );
}

register_activation_hook( __FILE__, 'schedule_csv_import' );

function csv_importer_form() {
    ?>
    <form method="post" id="csv-importer-form">
        <label for="csv_url">Enter the URL of the CSV file to import:</label>
        <input type="text" name="csv_url" id="csv_url" />
        <input type="submit" value="Import CSV" />
    </form>
    <?php
}
