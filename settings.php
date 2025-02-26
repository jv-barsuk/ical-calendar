<?php

function ical_calendar_enqueue_admin_styles() {
    wp_enqueue_style('ical-calendar-admin-styles', plugin_dir_url(__FILE__) . 'css/admin.css');
}
add_action('admin_enqueue_scripts', 'ical_calendar_enqueue_admin_styles');

// Display the settings page
function ical_calendar_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e("iCal Calendar Settings"); ?></h1>
        <form method="post">
            <?php wp_nonce_field('ical_fetch_nonce'); //value to validate that it was sent from admin?>
            <input type="hidden" name="ical_fetch_trigger" value="1">
            <?php submit_button(__('Reload iCal Events from URL')); ?>
            <span>Last updated: <?php 
                $dt = new IntlDateFormatter(get_locale(),IntlDateFormatter::FULL,IntlDateFormatter::NONE,null,IntlDateFormatter::GREGORIAN,"YYYY-MM-dd HH:mm");
                echo $dt->format(get_option('config_last_update'));?>
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
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('iCal Link');?></th>
                    <td>
                        <input type="text" name="ical_url" value="<?php echo esc_attr($ical_url); ?>" size="50" />
                        <p class="tagline-description"><?php _e('URL of a .ical/.ics file to fetch the events from');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Hide events older than (days)');?></th>
                    <td>
                        <input type="text" name="config_keep_duration" value="<?php echo esc_attr(get_option('config_keep_duration')); ?>"/>
                        <p class="tagline-description"><?php _e('Events older than this value in days will not be shown. Leave empty to disable.');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Show events after');?></th>
                    <td>
                        <input type="text" name="config_first_date" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($config_first_date); ?>"/>
                        <p class="tagline-description"><?php _e('All events before this date will not be displayed. If empty all events are shown.');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Show events before');?></th>
                    <td>
                        <input type="text" name="config_last_date" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($config_last_date); ?>"/>
                        <p class="tagline-description"><?php _e('All events after this date will not be displayed. If empty all events are shown.');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Date format');?></th>
                    <td>
                        <input type="text" name="config_date_format" value="<?php echo esc_attr($config_date_format); ?>"/>
                        <p class="tagline-description"><?php _e('e.g. E > day of the week (short); d > day of the month; M > month numeric; MMM > month short abbreviation; YYYY > year');?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Group month');?></th>
                    <td>
                        <input type="checkbox" name="config_group_month" value="1" <?php checked(get_option('config_group_month'), 1); ?> />
                        <p class="tagline-description"><?php _e('Enable grouping events by month');?></p>
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
                    <th scope="row"><?php _e('Labels');?></th>
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
    // Check if the button is clicked
    if (isset($_POST['ical_fetch_trigger']) && check_admin_referer('ical_fetch_nonce')) {
        ical_fetch_and_store_events(); // Manually trigger the function
        echo '<div class="updated"><p>' . __('iCal events fetched successfully!') . '</p></div>';
    }

    register_setting('ical_calendar_options', 'ical_url'); //TODO: validate if url
    register_setting('ical_calendar_options', 'config_first_date'); //TODO: validate if date
    register_setting('ical_calendar_options', 'config_last_date'); //TODO: validate if date
    register_setting('ical_calendar_options', 'config_date_format'); //TODO: validate if date format
    register_setting('ical_calendar_options', 'config_column_date');
    register_setting('ical_calendar_options', 'config_column_labels');
    register_setting('ical_calendar_options', 'config_column_summary');
    register_setting('ical_calendar_options', 'config_column_level');
    register_setting('ical_calendar_options', 'config_column_time');
    register_setting('ical_calendar_options', 'config_column_location');
    register_setting('ical_calendar_options', 'config_column_contact');
    register_setting('ical_calendar_options', 'config_event_labels');
    register_setting('ical_calendar_options', 'config_keep_duration'); //validate if number
    register_setting('ical_calendar_options', 'config_group_month'); // validate if bool
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