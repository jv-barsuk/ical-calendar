<?php

/**
 * Fetch events from url and store them
 */
function ical_fetch_and_store_events() {
    $ical_url = get_option('ical_url', '');
    $response = wp_remote_get($ical_url);

    if (is_wp_error($response)) {
        return;
    }

    update_option('config_last_update', new DateTime());

    $ical_content = wp_remote_retrieve_body($response);
    $events = ical_parse_events($ical_content);
    
    update_option('ical_fetched_events', $events);
}

/**
 * Function to parse iCal data
 */
function ical_parse_events($ical_content) {
    $events = [];
    preg_match_all('/BEGIN:VEVENT.*?END:VEVENT/s', $ical_content, $matches);
    foreach ($matches[0] as $event_data) {
        preg_match('/SUMMARY:(.*)/', $event_data, $summary);
        // TODO: Timezone not considered: DTSTART;TZID=Europe/Berlin:20241021T183000
        preg_match('/DTSTART.*?:(\d{8}(T\d{6}Z?)?)/', $event_data, $start); 
        preg_match('/DTEND.*?:(\d{8}(T\d{6}Z?)?)/', $event_data, $end);
        preg_match('/LOCATION:(.*)/', $event_data, $location);
        preg_match('/DESCRIPTION:(.*)/', $event_data, $description);

        $summary = str_replace("\,",",",$summary);
        $location = str_replace("\,",",",$location);
        $description = str_replace("\,",",",$description);


        $config_first_date = get_option('config_first_date', '');
        $config_last_date = get_option('config_last_date', '');
        $config_date_format = get_option('config_date_format', 'E d. MMM'); // 'E' = Weekday short, 'd' = Day, 'MMM' = Short Month

        $config_ww_level = '/(WW [-IV\+ ]+)+/';
        $config_contact = '/CONTACT:(.*?)(\\\n|$)/';
        $config_labels = '/(?:GROUP|LABELS):(.*?)(\\\n|$)/';

        if(isDateInRange($start[1], $config_first_date, $config_last_date)) {
            $events[] = [
                'start_date_plain' => ical_format_date($start[1], 'YMMdd'),
                'end_date_plain' => ical_format_date($end[1], 'YMMdd'),
                'start_date'   => ical_format_date($start[1], $config_date_format),
                'end_date'     => ical_format_date($end[1], $config_date_format),
                'summary'      => trim_string_by_pattern($config_ww_level, $summary[1]),
                'level'        => extract_string_by_pattern($config_ww_level, $summary[1]),
                'labels'       => labels_icons(extract_string_by_pattern($config_labels, $description[1] ?? "")),
                'start_time'   => ical_format_time($start[1], $config_date_format),
                'location'     => trim($location[1] ?? ""),
                'contact'      => extract_string_by_pattern($config_contact, $description[1] ?? "")

            ];
        }
    }

    //sort events
    usort($events, function ($a, $b) {
        return strtotime($a['start_date_plain']) - strtotime($b['start_date_plain']);
    });

    return $events;
}

/**
 * Replace labels, tags, labelss by an icon defined in the settings
 */
function labels_icons($string) {
    $rows = explode("\n",get_option('config_event_labels'));
    foreach ($rows as $row) {
        $fields = explode(";",$row);
        $string = str_replace($fields[0],'<span title="' . $fields[1] . '">' . $fields[2] . '</span>',$string);
    }

    return $string;
}

/**
 * Remove pattern from string; makes sence if pattern is used as separate field
 */
function trim_string_by_pattern($pattern, $string) {
    $string = preg_replace($pattern, "", $string);
    $string = trim($string);
    return $string;
}

/**
 * Extract a string matching a pattern from a string
 */
function extract_string_by_pattern($pattern, $string) {
    if(preg_match($pattern, $string, $matches)) {
        return $matches[1];
    }
    return "";
    
}

/**
 * Check if date is in allowed/desired range
 */
function isDateInRange($date_string, $first, $last) {
    $first = ($first == "") ? '1970-01-01' : $first;
    $first_date = new DateTime($first);
    $last = ($last == "") ? '2100-01-01' : $last;
    $last_date = new DateTime($last);
    $date = new DateTime($date_string);
    if($date >= $first_date && $date <= $last_date) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function to format iCal date
 */
function ical_format_date($ical_date, $date_format) {
    $date = new DateTime($ical_date);
    $formatter = new IntlDateFormatter(get_locale(), IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern($date_format); 
    return $formatter->format($date);
}


/**
 * Function to format iCal time
 */
function ical_format_time($ical_date) {
    return gmdate('H:i', strtotime($ical_date));
}