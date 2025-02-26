<?php

function ical_calendar_enqueue_admin_styles() {
    wp_enqueue_style('ical-calendar-admin-styles', plugin_dir_url(__FILE__) . 'css/admin.css', array(), ICAL_CALENDAR_PLUGIN_VERSION, 'all');
}
add_action('admin_enqueue_scripts', 'ical_calendar_enqueue_admin_styles');

// Display the settings page
function ical_calendar_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e("iCal Calendar Settings",'ical-calendar'); ?></h1>
        <form method="post">
            <?php wp_nonce_field(-1, 'ical_calendar_nonce_update_ical'); //value to validate that it was sent from admin?>
            <input type="hidden" name="ical_calendar_update_ical" value="1">
            <?php submit_button(__('Reload iCal Events from URL','ical-calendar')); ?>
            <span>Last updated: <?php 
                $dt = new IntlDateFormatter(get_locale(),IntlDateFormatter::FULL,IntlDateFormatter::NONE,null,IntlDateFormatter::GREGORIAN,"YYYY-MM-dd HH:mm");
                echo esc_html($dt->format(get_option('config_last_update')));?>
            </span>
        </form>
        <hr/>
        <form method="post" action="options.php">
            <?php
            settings_fields('ical_calendar_options');
            do_settings_sections('ical-calendar');
            $ical_url = get_option('ical_url', '');
            $config_first_date = get_option('config_first_date', '');
            $config_last_date = get_option('config_last_date', '');
            $config_date_format = get_option('config_date_format', 'E d. MMM');

            ?>
            <?php wp_nonce_field(-1,'ical_calendar_nonce_save_settings'); //value to validate that it was sent from admin?>
            <input type="hidden" name="ical_calendar_save_settings" value="1">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('iCal Link','ical-calendar');?></th>
                    <td>
                        <input type="text" name="ical_url" value="<?php echo esc_attr($ical_url); ?>" size="50" />
                        <p class="tagline-description"><?php esc_html_e('URL of a .ical/.ics file to fetch the events from','ical-calendar');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Hide events older than (days)','ical-calendar');?></th>
                    <td>
                        <input type="text" name="config_keep_duration" value="<?php echo esc_attr(get_option('config_keep_duration')); ?>"/>
                        <p class="tagline-description"><?php esc_html_e('Events older than this value in days will not be shown. Leave empty to disable.','ical-calendar');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Show events after','ical-calendar');?></th>
                    <td>
                        <input type="text" name="config_first_date" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($config_first_date); ?>"/>
                        <p class="tagline-description"><?php esc_html_e('All events before this date will not be displayed. If empty all events are shown.','ical-calendar');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Show events before','ical-calendar');?></th>
                    <td>
                        <input type="text" name="config_last_date" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($config_last_date); ?>"/>
                        <p class="tagline-description"><?php esc_html_e('All events after this date will not be displayed. If empty all events are shown.','ical-calendar');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Date format','ical-calendar');?></th>
                    <td>
                        <input type="text" name="config_date_format" value="<?php echo esc_attr($config_date_format); ?>"/>
                        <p class="tagline-description"><?php esc_html_e('e.g. E > day of the week (short); d > day of the month; M > month numeric; MMM > month short abbreviation; YYYY > year','ical-calendar');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Group month','ical-calendar');?></th>
                    <td>
                        <input type="checkbox" name="config_group_month" value="1" <?php checked(get_option('config_group_month'), 1); ?> />
                        <p class="tagline-description"><?php esc_html_e('Enable grouping events by month','ical-calendar');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Column Titles</th>
                    <td>
                        <table>
                            <tr><td><span>Date:</span></td><td><input type="text" name="config_column_date" value="<?php echo esc_attr(get_option('config_column_date')); ?>"/></td></tr>
                            <tr><td><span>Labels (parsed from description):</span></td><td><input type="text" name="config_column_labels" value="<?php echo esc_attr(get_option('config_column_labels')); ?>"/></td></tr>
                            <tr><td><span>Summary:</span></td><td><input type="text" name="config_column_summary" value="<?php echo esc_attr(get_option('config_column_summary')); ?>"/></td></tr>
                            <tr><td><span>Level (parsed from summary):</span></td><td><input type="text" name="config_column_level" value="<?php echo esc_attr(get_option('config_column_level')); ?>"/></td></tr>
                            <tr><td><span>Time (start):</span></td><td><input type="text" name="config_column_time" value="<?php echo esc_attr(get_option('config_column_time')); ?>"/></td></tr>
                            <tr><td><span>Location:</span></td><td><input type="text" name="config_column_location" value="<?php echo esc_attr(get_option('config_column_location')); ?>"/></td></tr>
                            <tr><td><span>Contact (parsed from description):</span></td><td><input type="text" name="config_column_contact" value="<?php echo esc_attr(get_option('config_column_contact')); ?>"/></td></tr>
                        </table>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Labels','ical-calendar');?></th>
                    <td>
                        <textarea name="config_event_labels" rows="10" cols="50"><?php echo esc_attr(get_option('config_event_labels')); ?></textarea>
                        <p class="tagline-description">Use the format label_to_replace;tooltip text;replacement (can be an icon)</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <hr>
        <div id="wrap">
        <h2 class="toggle-header">
            <span class="ical-calendar-arrow"></span>
            Documentation
        </h2>
        <div class="toggle-content">
            <p>iCal Calendar fetches events from an .ical/.ics URL and displays them as a table on your WordPress page.</p>
            <h3>Usage</h3>
            <p>Use the shortcode <code>[ical_events]</code> to insert the calendar into your page or post.</p>
            <p>In the current version, only one calendar is supported, which should be sufficient for most websites.</p>
            <p>Calendars are automatically refreshed every hour using WordPress cron jobs.</p>
            <h3>Parsing Special Information</h3>
            <p>Considering its intended purpose, iCal Calendar can parse certain information from the subject and description fields. This functionality is still partly hardcoded and will be made more flexible in the future, but it won't affect you if you don't need it. The information parsed includes:</p>
            <table>
                <tr><th>Column</th><th>Section</th><th>Keyword</th></tr>
                <tr><td>Labels</td><td>Description</td><td><code>LABELS:</code> (Deprecated: <code>GROUP:</code>)</td></tr>
                <tr><td>Level</td><td>Summary</td><td><code>Regex(/(WW [-IV\+ ]+)+/)</code></td></tr>
                <tr><td>Contact</td><td>Description</td><td><code>CONTACT:</code></td></tr>
            </table>
            <p>Information parsed from Labels can be replaced by entering the desired information in the labels field. 
                <code>kids;also for children;🐣</code> replaces the value <code>kids</code> with the 🐣 emoji and fills 
                the <code>title</code> attribute with <code>also for children</code>, so you get more information when hovering over the emoji.
                Multiple replacements are supported.
            </p>
            <h3>Limitations</h3>
            <ul>
                <li>iCal Calendar does not yet support time zones.</li>
                <li>Repeating events have not yet been tested.</li>
                <li>After changing the settings, you might have to reload the data for the changes to take effect.</li>
            </ul>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('.toggle-header').click(function() {
            $(this).next('.toggle-content').slideToggle(300);
            $(this).find('.ical-calendar-arrow').toggleClass('active');
        });
    });
    </script>
    <?php
}


// Register the settings
// To this after the admin page has fully loaded
add_action('admin_init', 'ical_calendar_register_settings');

/**
 * Register the options which can be saved in the settings
 */
function ical_calendar_register_settings() {
    if (isset($_POST['ical_calendar_update_ical']) && check_admin_referer(-1, 'ical_calendar_nonce_update_ical')) {
        ical_fetch_and_store_events(); // Manually trigger the function
        echo '<div class="updated"><p>' . esc_html(__('iCal events fetched successfully!','ical-calendar')) . '</p></div>';
    }

    if (isset($_POST['ical_calendar_save_settings']) && check_admin_referer(-1, 'ical_calendar_nonce_save_settings')) {

        ical_fetch_and_store_events();

        register_setting('ical_calendar_options', 'ical_url', 'esc_url'); // Sanitize URL)
        register_setting('ical_calendar_options', 'config_first_date', 'ical_calendar_sanitize_date'); // Custom date validation)
        register_setting('ical_calendar_options', 'config_last_date', 'ical_calendar_sanitize_date'); // Custom date validation)
        register_setting('ical_calendar_options', 'config_date_format', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_date', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_labels', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_summary', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_level', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_time', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_location', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_column_contact', 'sanitize_text_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_event_labels', 'sanitize_textarea_field'); // Sanitize as text)
        register_setting('ical_calendar_options', 'config_keep_duration', 'ical_calendar_sanitize_number'); // Custom number validation)
        register_setting('ical_calendar_options', 'config_group_month', 'ical_calendar_sanitize_checkbox'); // Validate as a boolean (checkbox)

    }    
}

// Custom sanitization function for date
function ical_calendar_sanitize_date($input) {
    $date = DateTime::createFromFormat('Y-m-d', $input);
    return $date ? $date->format('Y-m-d') : '';
}

// Custom sanitization function for numbers
function ical_calendar_sanitize_number($input) {
    return is_numeric($input) ? intval($input) : ""; // Return the number or 0 if not valid
}

// Custom sanitization function for checkboxes
function ical_calendar_sanitize_checkbox($input) {
    return ($input === '1' ? '1' : '0'); // Return '1' if checked, '0' otherwise
}


// Hook to create a menu item in the admin area
add_action('admin_menu', 'ical_calendar_menu');

function ical_calendar_menu() {
    add_options_page(
        'iCal Calendar Settings',    // Page title
        'iCal Calendar',             // Menu title
        'manage_options',           // Capability
        'ical-calendar',             // Menu slug
        'ical_calendar_settings_page' // Function to display the settings page
    );
}

?>