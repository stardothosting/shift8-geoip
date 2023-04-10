=== Shift8 GEO IP Location ===
* Contributors: shift8
* Donate link: https://www.shift8web.ca
* Tags: geolocation, geo location, geographic location, ip geolocation, ip address location, ip location, ip address location, ip address, ip tracking, geo ip location
* Requires at least: 3.0.1
* Tested up to: 6.2
* Stable tag: 1.08
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

Plugin that utilizes [ip-api](http://ip-api.com) to get geolocation coordinates based on the end-users' IP address. Read the [blog post detailing how to interact with the plugin](https://www.shift8web.ca/2018/01/wordpress-plugin-get-geolocation-coordinates-visitors-ip-address/).

== Want to see the plugin in action? ==

You can view three example sites where this plugin is live :

- Example Site 1 : [Wordpress Hosting](https://www.stackstar.com "Wordpress Hosting")
- Example Site 2 : [Web Design in Toronto](https://www.shift8web.ca "Web Design in Toronto")

= Features =

- Cookie session established with IP address and latitude / longitude coordinates of the end-user browsing the site

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/shif8-geoip` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the plugin settings page and define your settings
3. Once enabled, the system should trigger for every site visit.

== Frequently Asked Questions ==

= I tested it on myself and its not working for me! =

Try clearing all cookies and re-visit the website. Then examine the cookies set by the website in your browser. The cookie name should be "shift8_geoip".

= How do I read and use the shift8_geoip cookie? =

The plugin will set the cookie after successful geolocation and encrypt the value using OpenSSL with wp_salt('auth') as a key. This means in order to access the geolocation data you have to write code (in functions.php for example) such as the following :

`$cookie_data = explode('_', shift8_geoip_decrypt(wp_salt('auth'), $_COOKIE['shift8_geoip']));`

The cookie data when decrypted will look like the following :

`ipaddress_latitude_longitude`

Which means you can use php's explode function to convert it into an array as in the above example

= How can I decrypt the cookie data? You encrypted it! =

Well the data could be construed as somewhat sensitive and could be used maliciously to (for the most part) geographically place the end user. The decision to encrypt the cookie data was made to protect the user from the data falling into the wrong hands. In the plugin code, we are using OpenSSL to encrypt/decrypt the geo-location data. You can use the function below to decrypt the cookie data :

`// Function to decrypt session data
function shift8_geoip_decrypt($key, $garble) {
    if (!empty($key) && !empty($garble)) {
        list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    } else {
        return false;
    }
}`

So to actually use the above function, you could do the following :

`shift8_geoip_decrypt(wp_salt('auth'), $_COOKIE['shift8_geoip']);`

= What is wp_salt? =

You can read more about the wp_salt function by [clicking here](https://codex.wordpress.org/Function_Reference/wp_salt)


== Screenshots ==

1. Admin area 

== Changelog ==

= 1.0 =
* Stable version created

= 1.01 =
* Switched from stored session variable to encrypted cookie using wp_salt function. This is to easily allow development options to read and process the cookie data

= 1.02 =
* Updated readme with helpful FAQ entries

= 1.03 =
* Better error checking with geoip class and returned array

= 1.04 =
* If no valid IP is found in the get_ip function, the last return value still needs to be chcecked if a valid IP, otherwise return false

= 1.05 =
* Now including country code in geoip encrypted cookie

= 1.06 =
* Wordpress 5 compatibility

= 1.07 =
* Wordpress 5.5 compatibility

= 1.08 =
* Wordpress 6.2 compatibility