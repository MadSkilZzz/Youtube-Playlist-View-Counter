<?php

    if (!file_exists('common.php')) {
        die('Error: common.php not found!');
    }
    include_once 'common.php';

    // Get playlist URL from user input
    echo "Enter YouTube playlist URL: ";
    $handle = fopen("php://stdin", "r");
    $url = trim(fgets($handle));
    fclose($handle);

    // Extract playlist ID from the URL
    parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
    $id = $queryParams['list'] ?? '';

    // Validate `id`
    if (!function_exists('isPlaylistId')) {
        die('Error: isPlaylistId() function is missing!');
    }
    if (!isPlaylistId($id)) {
        die('Invalid playlist ID');
    }

    $viewCount = getViewCount($id);
    echo "Views: $viewCount\n";

    function getViewCount($id) {
        $opts = [
            "http" => [
                "header" => [
                    'Cookie: CONSENT=YES+', 
                    'Accept-Language: en'
                ]
            ]
        ];

        if (!function_exists('getJSONFromHTML')) {
            die('Error: getJSONFromHTML() function is missing!');
        }

        $result = getJSONFromHTML('https://www.youtube.com/playlist?list=' . $id, $opts);

        if (!$result) {
            die('Error: Failed to retrieve playlist data.');
        }

        $path = 'sidebar/playlistSidebarRenderer/items/0/playlistSidebarPrimaryInfoRenderer/stats/1/simpleText';
        $viewCount = getNestedValue($result, $path);

        return $viewCount ? intval(str_replace([' views', ' view', ','], '', $viewCount)) : 0;
    }

    function getNestedValue($array, $path) {
        $keys = explode('/', $path);
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return null;
            }
            $array = $array[$key];
        }
        return $array;
    }

?>
