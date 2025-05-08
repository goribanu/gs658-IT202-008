<?php
// string array containing env keys to lookup (this allows usage of multiple APIs)
$env_keys = ["OMDB_API_KEY"];
$ini = @parse_ini_file(".env");

$API_KEYS = [
    "OMDB_API_KEY" => "5a599b6d"
];

foreach ($env_keys as $key) {
    if ($ini && isset($ini[$key])) {
        //load local .env file
        $API_KEY = $ini[$key];
        $API_KEYS[$key] = $API_KEY;
    } else {
        //load from heroku env variables
        $API_KEY = getenv($key);
        $API_KEYS[$key] = $API_KEY;
    }
    if (!isset($API_KEYS[$key]) || !$API_KEYS[$key]) {
        error_log("Faild to load api key for env key $key");
    }
    unset($API_KEY);
}

error_log("Loaded API Keys: " . var_export($API_KEYS, true));