<?php
$env_keys = ["OMDB_API_KEY"];
$ini = @parse_ini_file(".env");

$API_KEYS = [
    "OMDB_API_KEY" => "5a599b6d" // default fallback
];

foreach ($env_keys as $key) {
    $env_value = $ini[$key] ?? getenv($key);
    if (!empty($env_value)) {
        $API_KEYS[$key] = $env_value;
    } else {
        error_log("Failed to load API key for env key $key");
    }
}

error_log("Loaded API Keys: " . var_export($API_KEYS, true));