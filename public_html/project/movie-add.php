<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid">
    <h1>Add Movie</h1>
    <form onsubmit="return validate(this)" method="POST">
        <div>
            <label>Movie Title</label>
            <input name="title" />
            <label>Year Released</label>
            <input name="year" />
            <label>Rating (out of 5)</label>
            <input name="rating" />
            <input type="submit" value="Add" />
        </div>
    </form>

    <script>
        function validate(form) {
            // implement JavaScript validation
            //ensure it returns false for previously existing movies and true 
            // if successfully added
            let isValid = true;
            const title = form.title.value;
            const year = form.year.value;
            const rating = form.rating.value;
            if (title.length === 0 || title === null) {
                flash("Empty Title", "danger");
                isValid = false;
            }
            if (year.length === 0 || year === null) {
                flash("Empty Year", "danger");
                isValid = false;
            }
            return isValid;
        }
    </script>
</div>
<?php
// PHP Code
if (isset($_POST["title"]) && isset($_POST["year"])) {
    $title = se($_POST, "title", "", false); //$_POST["title"];
    $year = se($_POST, "year", "", false); //$_POST["year"];
    $rating = se($_POST, "rating", "", false); //$_POST["rating"];
    $is_api = 0; // 0 for custom form
    $plot = "";

    $hasError = false;
    if (empty($title)) {
        flash("Title must be provided <br>");
        $hasError = true;
    }

    if (empty($year)) {
        flash("Year must be provided <br>");
        $hasError = true;
    }

    $results = [];
    if (isset($_POST["title"])) {
        // Load your OMDB API key
        global $API_KEYS;
        $apiKey = $API_KEYS["OMDB_API_KEY"] ?? "";
    
        $data = [
            "t" => $_POST["title"],
            "type" => "movie",
            "apikey" => $apiKey // <-- key as query param, not a header
        ];
    
        $endpoint = "https://www.omdbapi.com";
        $isRapidAPI = false;
        $results = get($endpoint, $apiKey, $data, $isRapidAPI); // null for key label
    
        error_log("OMDB Response: " . var_export($results, true));
    
        if (se($results, "status", 400, false) == 200 && isset($results["response"])) {
            $results = json_decode($results["response"], true); 
            $is_api = 1;
            $plot = $results["Plot"] ?? "Plot not available";
        } else {
            $results = [];
        }
    }

    if (!$hasError) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, title, year, rating from Movies where title = :title");
        try {
            $r = $stmt->execute([":title" => $title]);
            if ($r) {
                $existing = $stmt->fetch(PDO::FETCH_ASSOC); // <-- use a new variable here
                if ($existing) {
                    flash("Title already found");
                } else {
                    $stmt = $db->prepare("INSERT INTO Movies (title, year, rating, is_api, plot) VALUES (:title, :year, :rating, BINARY :is_api, :plot)");
                    $r = $stmt->execute([
                        ":title" => $title,
                        ":year" => $year,
                        ":rating" => $rating,
                        ":is_api" => $is_api,
                        ":plot" => $plot
                    ]);

                    if ($r) {
                        flash("Movie added successfully!", "success");
                    } else {
                        flash("Failed to add movie", "danger");
                    }
                }
            }
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
