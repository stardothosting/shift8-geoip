<?php

class SHIFT8_GEOIP_IPAPI {
    static $fields = 65535;     // refer to http://ip-api.com/docs/api:returned_values#field_generator
    static $api = "http://ip-api.com/php/";

    public $status, $country, $countryCode, $region, $regionName, $city, $zip, $lat, $lon, $timezone, $isp, $org, $as, $reverse, $query, $message;

    public static function query($q) {
        try {
            $data = self::communicate($q);
            $result = new static;
            foreach($data as $key => $val) {
                $result->$key = $val;
            }
            return $result;
        }
        catch (Exception $e) {
            return null;
        }

    }

    private function communicate($q) {
        $result_array = wp_remote_get( self::$api.$q.'?fields='.self::$fields,
            array(
                'httpversion' => '1.1',
                'timeout' => 30,
            )
        );

        if (is_array($result_array)) {
            return unserialize($result_array['body']);
        } else {
            return null;
        }
    }
}
