<?php

// Function to initialize & check for session 
function shift8_geoip_init() {
	// Grab the encryption key (which is wp_salt auth key)
	$encryption_key = wp_salt('auth');

    // Initialize only if enabled
    if (shift8_geoip_check_options()) {
        // Get the end-user's IP address
        $ip_address = shift8_geoip_get_ip();

        // If the session isnt set
        if (!isset($_COOKIE['shift8_geoip'])) {
            // Only set the session if a valid IP address was found
            if ($ip_address) {
                $query = SHIFT8_GEOIP_IPAPI::query($ip_address);
                $cookie_data = shift8_geoip_encrypt($encryption_key, $ip_address . '_' . $query->lat . '_' . $query->lon . '_' . $query->countryCode);
                setcookie('shift8_geoip', $cookie_data, strtotime('+1 day'), '/');
            }

        // If the cookie is set
        } else {
            // if session is set, validate it and remove if not valid
            $cookie_data = explode('_', shift8_geoip_decrypt($encryption_key, $_COOKIE['shift8_geoip']));

            // If the ip address doesnt match the value of the session OR if the timestamp of the session is in the past
            if (esc_attr($cookie_data[0]) != $ip_address) {
                clear_shift8_geoip_cookie();

            // If there's an error set in the cookie, clear and then set a temp cookie that expires sooner
            } else if (esc_attr($cookie_data[1]) == 'error') {
                // Unset the existing session, re-set it with a shorter expiration time
                clear_shift8_geoip_cookie();
                // Set the ip address but clear any GeoLocation values for now
                $cookie_newdata = shift8_geoip_encrypt($encryption_key, esc_attr($cookie_data[0]) . '_ignore_ignore');
				setcookie('shift8_geoip', $cookie_newdata, strtotime('+1 hour'), '/');
				
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

// Function to encrypt session data
function shift8_geoip_encrypt($key, $payload) {
    if (!empty($key) && !empty($payload)) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    } else {
        return false;
    }
}

// Function to decrypt session data
function shift8_geoip_decrypt($key, $garble) {
    if (!empty($key) && !empty($garble)) {
        list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    } else {
        return false;
    }
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
    return false;
}

// Validate IP address using filter_var function
function shift8_geoip_validate_ip($ip) {
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
		return false;
	}
	return true;
}

// Validate admin options
function shift8_geoip_check_options() {
    // If enabled is not set 
    if(esc_attr( get_option('shift8_geoip_enabled') ) != 'on') return false;
    // If none of the above conditions match, return true
    return true;
}
