<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 11/04/2017
 * Time: 13:47
 */


function extension_register_module($ThemifyBuilder ) {
    $ThemifyBuilder->register_directory( 'templates', plugin_dir_path( __FILE__ ) . '/templates' );
    $ThemifyBuilder->register_directory( 'modules', plugin_dir_path( __FILE__ ) . '/modules' );
}

add_action( 'themify_builder_setup_modules', 'extension_register_module');