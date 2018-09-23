<?php
/**
 * Plugin Name: Plugin Update Notifications
 * Plugin URI:
 * Description: A simple plugin to disable plugin update notifications.
 * Version: 1.0
 * Author: Daren Wesolowski
 * Author URI:
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright (C) 2018  Daren Wesolowski
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // exit if accessed directly!

class pluginUpdateNotifications {

    public function __construct() {
        global $excluded_plugins;
        $excluded_plugins = get_option( 'plugin_update_notifications_excluded_plugins' ) ?: array();

        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_options_menu' ) );
        add_filter( 'site_transient_update_plugins', array( $this, 'disable_plugin_update_notifications' ) );
        register_deactivation_hook( __FILE__ , array( $this, 'plugin_update_notifications_deactivation' ) );
    }

    public function register_settings() {
        register_setting(
            'plugin_update_notifications_excluded_plugins_group',
            'plugin_update_notifications_excluded_plugins'
        );
    }

    public function add_options_menu() {
        add_options_page(
            'Plugin Update Notifications',
            'Update Notifications',
            'manage_options',
            'plugin_update_notifications_options',
            array( $this, 'plugin_update_notifications_options_page' )
        );
    }

    public function plugin_update_notifications_options_page() {

        if ( ! current_user_can( 'manage_options' ) ) return;

        global $excluded_plugins;
        $current_plugins = get_plugins();

        echo '<div class="wrap">';
        echo '<h2>'.esc_html( get_admin_page_title() ).'</h2>';
        echo '<form action="options.php" method="post">';
        settings_fields( 'plugin_update_notifications_excluded_plugins_group' );
        echo '<h2>Disable Notifications for the following Plugins</h2> Showing all currently installed plugins both active and inactive.';
        echo '<table class="form-table">';
        echo '<tbody>';
        foreach ( $current_plugins as $key => $val ) {
            echo '<tr>';
            echo '<th scope="row" colspan="2" class="th-full">';
            echo '<label for="'.$key.'">';
            echo '<input type="checkbox" name="plugin_update_notifications_excluded_plugins[]" value="'.$key.'" '.( ( isset( $excluded_plugins ) && in_array ( $key, $excluded_plugins ) ) ? 'checked="checked"' : "" ).'> '.$val['Name'].' - v'.$val['Version'].' </label>';
            echo '</th>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function disable_plugin_update_notifications( $value ) {
        global $excluded_plugins;

        if ( isset( $value ) && is_object( $value ) ) {
            foreach ( $excluded_plugins as $plugin ) {
                if ( isset( $value->response[$plugin] ) ) {
                    unset( $value->response[$plugin] );
                }
            }
        }
        return $value;
    }

    public function plugin_update_notifications_deactivation() {
        delete_option( 'plugin_update_notifications_excluded_plugins' );
    }
}
$pluginUpdateNotifications = new pluginUpdateNotifications();
