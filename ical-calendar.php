<?php
/**
 * Plugin Name: iCal Calendar
 * Description: Fetches an iCal (.ical/.ics) file from an URL and displays the events using a shortcode.
 * Version: 1.0
 * Author: me1es
 * Text Domain: ical-calendar
 * License: GPL-2.0-or-later
 * License URI: https://opensource.org/licenses/GPL-2.0
 */

 define('ICAL_CALENDAR_PLUGIN_VERSION', '1.0.0');

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// include settings file
require_once plugin_dir_path(__FILE__) . 'settings.php'; // Everything about the settings
require_once plugin_dir_path(__FILE__) . 'parser.php'; // Parsing the ICS file
require_once plugin_dir_path(__FILE__) . 'output.php'; // Output the data in frontend


// Register and enqueue the plugin stylesheet
function ical_calendar_enqueue_styles() {
    wp_enqueue_style('ical-calendar-styles', plugin_dir_url(__FILE__) . 'css/style.css', array(), ICAL_CALENDAR_PLUGIN_VERSION, 'all');
}
// Hook the function to load styles in the frontend
add_action('wp_enqueue_scripts', 'ical_calendar_enqueue_styles');



// Some cronjob parts need to be here in the main file

// Schedule cron job on plugin activation
function ical_calendar_activate() {
    if (!wp_next_scheduled('ical_calendar_cron')) {
        wp_schedule_event(time(), 'hourly', 'ical_calendar_cron');
    }

    // Check if the option already exists, if not, set a default value
    if (get_option('config_group_month') === false) {
        add_option('config_group_month', 1); // Default value set to 1 (checked)
    }
}


// Clear cron job on plugin deactivation
function ical_calendar_deactivate() {
    wp_clear_scheduled_hook('ical_calendar_cron');
}


register_activation_hook(__FILE__, 'ical_calendar_activate');
register_deactivation_hook(__FILE__, 'ical_calendar_deactivate');

// Cron job to fetch and parse iCal
add_action('ical_calendar_cron', 'ical_fetch_and_store_events');