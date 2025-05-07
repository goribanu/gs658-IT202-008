<?php
require(__DIR__ . "/../../partials/nav.php");

$results = [];
if (isset($_GET["title"])) {
    // Load your OMDB API key
    global $API_KEYS;
    $apiKey = $API_KEYS["OMDB_API_KEY"] ?? "";

    $data = [
        "s" => $_GET["title"],
        "type" => "movie",
        "apikey" => $apiKey // <-- key as query param, not a header
    ];

    $endpoint = "https://www.omdbapi.com";
    $isRapidAPI = false; // <-- OMDB is not on RapidAPI
    $results = get($endpoint, $apiKey, $data, $isRapidAPI); // null for key label

    error_log("OMDB Response: " . var_export($results, true));

    if (se($results, "status", 400, false) == 200 && isset($results["response"])) {
        $results = json_decode($results["response"], true);
    } else {
        $results = [];
    }
}
?>


<div class="container-fluid">
    <h1>Cinepeak Explorer</h1>
    <form>
        <div>
            <label>Movie Title</label>
            <input name="title" />
            <input type="submit" value="Search" />
        </div>
    </form>
    <div class="row">
        <?php if (isset($results["Search"])) : ?>
            <?php foreach ($results["Search"] as $movie) : ?>
                <div class="col">
                    <div>
                        <h5><?= se($movie, "Title", "N/A") ?> (<?= se($movie, "Year", "N/A") ?>)</h5>
                        <img src="<?= se($movie, "Poster", "#") ?>" alt="Poster" style="width:100px;">
                    </div>
                </div>
            <?php endforeach; ?>
        <?php elseif (isset($results["Error"])) : ?>
            <p><?= se($results, "Error", "No results found.") ?></p>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");