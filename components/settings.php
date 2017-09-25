<?php

// create custom plugin settings menu
add_action('admin_menu', 'shift8_geoip_create_menu');
function shift8_geoip_create_menu() {
        //create new top-level menu
        if ( empty ( $GLOBALS['admin_page_hooks']['shift8-settings'] ) ) {
                add_menu_page('Shift8 Settings', 'Shift8', 'administrator', 'shift8-settings', 'shift8_main_page' , 'dashicons-building' );
        }
        add_submenu_page('shift8-settings', 'GEO IP Settings', 'GEO IP Settings', 'manage_options', __FILE__.'/custom', 'shift8_geoip_settings_page');
        //call register settings function
        add_action( 'admin_init', 'register_shift8_geoip_settings' );
}

// Register admin settings
function register_shift8_geoip_settings() {
    //register our settings
    register_setting( 'shift8-geoip-settings-group', 'shift8_geoip_enabled' );
}

