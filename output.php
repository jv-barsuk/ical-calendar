<?php 

// Shortcode to display events
function ical_events_shortcode() {
    $events = get_option('ical_fetched_events', []);

    if (empty($events)) {
        return '<p>' . __('No upcoming events.','ical-calendar') . '</p>';
    }

    register_setting('ical_calendar_options', 'config_column_date', 'sanitize_text_field'); 
    register_setting('ical_calendar_options', 'config_column_group', 'sanitize_text_field'); 
    register_setting('ical_calendar_options', 'config_column_summary', 'sanitize_text_field');
    register_setting('ical_calendar_options', 'config_column_level', 'sanitize_text_field');
    register_setting('ical_calendar_options', 'config_column_time', 'sanitize_text_field');
    register_setting('ical_calendar_options', 'config_column_location', 'sanitize_text_field');
    register_setting('ical_calendar_options', 'config_column_contact', 'sanitize_text_field');

    $output = '<table>';
    $output .= '<thead><tr>
    <th>'. get_option('config_column_date') .'</th>
    <th>'. get_option('config_column_group') .'</th>
    <th>'. get_option('config_column_summary') .'</th>
    <th>'. get_option('config_column_level') .'</th>
    <th>'. get_option('config_column_time') .'</th>
    <th>'. get_option('config_column_location') .'</th>
    <th>'. get_option('config_column_contact') .'</th>
    </tr></thead>';
    $month = "";
    foreach ($events as $event) {
        
        $config_keep_duration = get_option('config_keep_duration');
        $keep_duration = (int)$config_keep_duration * 60 * 60 * 24;
        $now = new DateTime();
        $end_date = new DateTime($event['end_date_plain']);
        if($config_keep_duration != "" && $now->getTimestamp() - $end_date->getTimestamp() > $keep_duration) {continue;}
        
        $current_event_month = ical_format_date($event['start_date_plain'], 'MMMM');
        if(get_option('config_group_month') && $current_event_month != $month) {
            $month = $current_event_month;
            $output .= '<tr class="month_separator" ><td colspan="7" >' . $month . '</td></tr>';
        }
        $output .= '<tr>';
        if(esc_html($event['start_date']) == esc_html($event['end_date'])) {
            $output .= '<td class="date">' . esc_html($event['start_date']); 
        } else {
            if(ical_format_date($event['start_date_plain'], 'M') == ical_format_date($event['end_date_plain'], 'M')) {
                // output month only once on a multy day event in the same month
                $config_date_format_start = get_option('config_date_format', 'E d. MMM');
                $config_date_format_start = preg_replace('/M+ *Y*/',"", $config_date_format_start);
                $output .= '<td class="date">' . esc_html(ical_format_date($event['start_date_plain'], $config_date_format_start)) . ' - ' . esc_html($event['end_date']) . '</td>';
            } else {
                $output .= '<td class="date">' . esc_html($event['start_date']) . ' - ' . esc_html($event['end_date']) . '</td>';
            }
        }
        $output .= '<td class="labels">' . $event['labels'] . '</td>';
        $output .= '<td class="summary" tabindex="0">' . esc_html($event['summary']) . '</td>';
        $output .= '<td>' . esc_html($event['level']) . '</td>';
        $output .= '<td class="start_time">' . esc_html($event['start_time']) . '</td>';
        $output .= '<td class="location">' . esc_html($event['location']) . '</td>';
        $output .= '<td class="contact">' . esc_html($event['contact']) . '</td>';
        $output .= '</tr>';
    }
    $output .= '</table>';

    return $output;
}
add_shortcode('ical_events', 'ical_events_shortcode');