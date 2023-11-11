<?php

namespace Piscarocarlos\Helpify;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Helpify
{
    /**
     * Shortens a given URL.
     *
     * @param string $url The URL to shorten.
     * @return string The shortened URL.
     */
    public function shorten(string $url): string
    {
        $contextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $apiUrl = "https://is.gd/create.php?format=simple&url=" . $url;
        $shortURL = file_get_contents($apiUrl, false, stream_context_create($contextOptions));

        return $shortURL;
    }

    /**
     * Check the client's IP address.
     *
     * @param string $ip The IP address to check.
     * @return bool True if the IP address is valid, false otherwise.
     */
    public function checkValidIpAddress($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }

    /**
     * Gets the client's IP address.
     *
     * This method checks several possible sources for the client's IP address,
     * such as HTTP headers and server variables.
     *
     * @return string The client's IP address.
     */
    public function getClientIp(): string
    {
        $ipAddress = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->checkValidIpAddress($_SERVER['HTTP_CLIENT_IP'])) {
            // check for shared ISP IP
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check for IPs passing through proxy servers
            // check if multiple IP addresses are set and take the first one
            $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipAddressList as $ip) {
                if ($this->checkValidIpAddress($ip)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        } else if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->checkValidIpAddress($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->checkValidIpAddress($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->checkValidIpAddress($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_FORWARDED']) && $this->checkValidIpAddress($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (!empty($_SERVER['REMOTE_ADDR']) && $this->checkValidIpAddress($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipAddress;
    }

    /**
     * Gets the client's browser.
     *
     * @return string The client's browser.
     */
    public function getClientBrowser(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $browser = 'UNKNOWN';

        if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Netscape/i', $userAgent)) {
            $browser = 'Netscape';
        }

        return $browser;
    }

    /**
     * Gets the client's operating system.
     *
     * @return string The client's operating system.
     */
    public function getClientOs(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $os = 'UNKNOWN';

        if (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'Mac OS X';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $os = 'Windows';
        }

        return $os;
    }

    /**
     * Gets the client's device.
     *
     * @return string The client's device.
     */
    public function getClientDevice(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $device = 'UNKNOWN';

        if (preg_match('/mobile/i', $userAgent)) {
            $device = 'Mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            $device = 'Tablet';
        } elseif (preg_match('/watch/i', $userAgent)) {
            $device = 'Watch';
        } elseif (preg_match('/tv/i', $userAgent)) {
            $device = 'TV';
        } elseif (preg_match('/bot/i', $userAgent)) {
            $device = 'Bot';
        } elseif (preg_match('/curl/i', $userAgent)) {
            $device = 'Curl';
        } elseif (preg_match('/wget/i', $userAgent)) {
            $device = 'Wget';
        }

        return $device;
    }

    /**
     * Gets the client's country.
     *
     * @return string The client's country.
     */
    public function getClientCountry(): string
    {
        $ipAddress = $this->getClientIp();

        $country = 'UNKNOWN';

        if ($ipAddress !== 'UNKNOWN') {
            $apiUrl = "https://ipapi.co/" . $ipAddress . "/country_name/";
            $country = file_get_contents($apiUrl);
        }

        return $country;
    }


    /**
     * Get the CSS class for the active route.
     *
     * This method compares the current route name with the given routes
     * and returns the specified output class if a match is found.
     *
     * @param array  $routes  The routes to check for activity.
     * @param string $output  The output class to return if a match is found (default: "active").
     *
     * @return string|null The output class if the current route matches any of the provided routes, otherwise null.
     */
    public function activeClass(array $routes, $output = "active"): ?string
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) {
                return $output;
            }
        }

        return null;
    }

    /**
     * Generates an HTML tag with attributes.
     *
     * @param string $tag       The tag name.
     * @param array  $attributes The attributes of the tag (optional).
     * @param string $content    The content of the tag (optional).
     *
     * @return string The generated HTML tag.
     */
    function htmlTag($tag, $attributes = [], $content = '')
    {
        $attributesStr = '';
        foreach ($attributes as $name => $value) {
            $attributesStr .= " $name=\"$value\"";
        }

        return "<$tag$attributesStr>$content</$tag>";
    }

    /**
     * Formats a date in the specified format.
     *
     * @param string            $format The date format (e.g., 'Y-m-d H:i:s').
     * @param DateTimeInterface $date   The DateTime object to format.
     *
     * @return string The formatted date.
     */
    function formatDate($format, \DateTimeInterface $date)
    {
        return $date->format($format);
    }


    /**
     * Truncates a string to a specified length.
     *
     * @param string $string   The string to truncate.
     * @param int    $length   The desired length.
     *
     * @return string The truncated string.
     */
    function truncateString($string, $length)
    {
        return mb_strimwidth($string, 0, $length, '...');
    }

    /**
     * Generates a URL-friendly slug from a given string.
     *
     * @param string $string The string to convert to a slug.
     *
     * @return string The generated slug.
     */
    function generateSlug($string)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $string), '-'));
        return preg_replace('/-+/', '-', $slug);
    }


    /**
     * Generates a random string of a specified length.
     *
     * @param int $length The length of the random string.
     *
     * @return string The generated random string.
     */
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }


    /**
     * Plucks a specific key from each element of an array.
     *
     * @param array  $array The array to pluck from.
     * @param string $key   The key to pluck.
     *
     * @return array The array of plucked values.
     */
    function arrayPluck($array, $key)
    {
        return array_map(function ($item) use ($key) {
            return is_object($item) ? $item->$key : $item[$key];
        }, $array);
    }


    /**
     * Converts a string to camel case.
     *
     * @param string $string The string to convert.
     *
     * @return string The camel case version of the string.
     */
    function toCamelCase($string)
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));
        return lcfirst(str_replace(' ', '', $string));
    }


    /**
     * Flattens a multi-dimensional array into a single level.
     *
     * @param array $array The array to flatten.
     *
     * @return array The flattened array.
     */
    function arrayFlatten($array)
    {
        $result = [];
        array_walk_recursive($array, function ($value) use (&$result) {
            $result[] = $value;
        });
        return $result;
    }


    /**
     * Generates an HTML link with the target attribute set for external links.
     *
     * @param string $url The URL of the link.
     * @param string $text The anchor text of the link.
     *
     * @return string The HTML link.
     */
    function externalLink($url, $text)
    {
        $target = parse_url($url, PHP_URL_HOST) != $_SERVER['HTTP_HOST'] ? 'target="_blank"' : '';
        return "<a href=\"$url\" $target>$text</a>";
    }


    /**
     * Shuffles an array using a seeded random number generator.
     *
     * @param array $array The array to shuffle.
     *
     * @return array The shuffled array.
     */
    function seededShuffle($array)
    {
        srand(42); // Seed for reproducibility
        shuffle($array);
        return $array;
    }


    /**
     * Generates a slug for a Laravel model instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance.
     * @param string                             $field The field to use for generating the slug.
     *
     * @return string The generated slug.
     */
    function generateModelSlug($model, $field = 'name')
    {
        return Str::slug($model->$field);
    }

    /**
     * Add or update a key-value pair in the .env file.
     *
     * @param string $key   The key to add or update.
     * @param string $value The value to set for the key.
     *
     * @return void
     */
    function writeToEnvFile($key, $value)
    {
        $envFilePath = base_path('.env');

        // Read the existing content of the .env file
        $currentEnvContent = file_get_contents($envFilePath);

        // Check if the key already exists in the .env file
        if (strpos($currentEnvContent, "$key=") !== false) {
            // Update the existing value
            $updatedEnvContent = preg_replace("/$key=(.*)/", "$key=$value", $currentEnvContent);
        } else {
            // Add a new key-value pair
            $updatedEnvContent = "$currentEnvContent\n$key=$value";
        }

        // Write the updated content back to the .env file
        file_put_contents($envFilePath, $updatedEnvContent);

        // Clear the Laravel configuration cache
        // (optional, but may be needed to reflect changes)
        Artisan::call('config:clear');
    }
}
