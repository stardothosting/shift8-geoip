<?php

// Function to initialize & check for session 
function shift8_geoip_init() {
    // Initialize only if enabled
    if (shift8_geoip_check_options()) {
        // Get the end-user's IP address
        $ip_address = shift8_geoip_get_ip();

        if (!session_id()) {
            session_start();
        }
        // If the session isnt set
        if (!isset($_COOKIE['shift8_geoip'])) {
            // Only set the session if a valid IP address was found
            if ($ip_address) {
                $query = SHIFT8_GEOIP_IPAPI::query($ip_address);
                $cookie_data = $ip_address . '_' . $query->lat . '_' . $query->lon;
                setcookie( 'shift8_geoip', $cookie_data, strtotime( '+1 day' ), '/');
            }
        // If the session is set
        } else {
            // if session is set, validate it and remove if not valid
            $cookie_data = explode('_', $_COOKIE['shift8_geoip']);
            // If the ip address doesnt match the value of the session OR if the timestamp of the session is in the past
            if (esc_attr($cookie_data[0]) != $ip_address) {
                clear_shift8_geoip_cookie();
            } else if (esc_attr($cookie_data[1]) == 'error_detected') {
                // Unset the existing session, re-set it with a shorter expiration time
                clear_shift8_geoip_cookie();
                // Set the ip address but clear any IP Intel values for now
                $cookie_newdata = esc_attr($cookie_data[0]) . '_ignore_ignore';
                // Generally if there is an error detected, its likely because you exceeded the threshold. Wait an hour before doing this process again
                setcookie( 'shift8_geoip', $cookie_newdata, strtotime( '+1 hour' ));
            }
        }
    }
}
add_action('init', 'shift8_geoip_init', 1);

// Common function to clear the session 
function clear_shift8_geoip_cookie() {
    unset($_COOKIE['shift8_geoip']);
    setcookie('shift8_geoip', '',  time()-3600, '/');
}

function shift8_geoip_get_ip() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                // trim for safety measures
                $ip = trim($ip);
                // attempt to validate IP
                if (shift8_geoip_validate_ip($ip)) {
                    return $ip;
                }
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

function shift8_geoip_validate_ip($ip)
{
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
		return false;
	}
	return true;
}

function shift8_geoip_check_options() {
    // If enabled is not set 
    if(esc_attr( get_option('shift8_geoip_enabled') ) != 'on') return false;
    // If none of the above conditions match, return true
    return true;
}
