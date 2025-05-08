<?php
require(__DIR__ . "/../../partials/nav.php");

// Handle adding to watchlist
if (isset($_POST["add_to_watchlist"]) && isset($_POST["movie_id"])) {
    $movie_id = (int)$_POST["movie_id"];
    $user_id = get_user_id(); // Assumes using session-based login

    if ($movie_id > 0 && $user_id > 0) {
        $db = getDB();

        // Prevent duplicates
        $check = $db->prepare("SELECT id FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
        $check->execute([":uid" => $user_id, ":mid" => $movie_id]);

        if ($check->fetch()) {
            flash("Movie already in watchlist", "warning");
        } else {
            $stmt = $db->prepare("INSERT INTO Watchlist (user_id, movie_id) VALUES (:uid, :mid)");
            $stmt->execute([":uid" => $user_id, ":mid" => $movie_id]);
            flash("Movie added to watchlist!", "success");
        }
    }
}

// Handle removing from watchlist
if (isset($_POST["remove_from_watchlist"]) && isset($_POST["movie_id"])) {
    $movie_id = (int)$_POST["movie_id"];
    $user_id = get_user_id();

    if ($movie_id > 0 && $user_id > 0) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
        $stmt->execute([":uid" => $user_id, ":mid" => $movie_id]);
        flash("Movie removed from watchlist", "info");
    }
}

?>

<div class="container-fluid">
    <h1>List of Movies</h1>

    <!-- gs658 - 5/1/25 - Filter and Sort Form, HTML validation -->
    <form method="GET" class="mb-3">
        <label for="title">Title:</label>
        <input type="text" name="title" value="<?= se($_GET, "title", "") ?>" />

        <label>Rating:</label>
        Min <input type="number" name="min_rating" min="0" max="5" step="0.5" value="<?= se($_GET, "min_rating", "") ?>" />
        Max <input type="number" name="max_rating" min="0" max="5" step="0.5" value="<?= se($_GET, "max_rating", "") ?>" />

        <label>Year:</label>
        Min <input type="number" name="min_year" min="1900" value="<?= se($_GET, "min_year", "") ?>" />
        Max <input type="number" name="max_year" max="3000" value="<?= se($_GET, "max_year", "") ?>" />

        <label for="sort_by">Sort by:</label>
        <select name="sort_by">
            <option value="title" <?= se($_GET, "sort_by", "") === "title" ? "selected" : "" ?>>Title</option>
            <option value="year" <?= se($_GET, "sort_by", "") === "year" ? "selected" : "" ?>>Year</option>
            <option value="rating" <?= se($_GET, "sort_by", "") === "rating" ? "selected" : "" ?>>Rating</option>
        </select>

        <label for="limit">Limit:</label>
        <select name="limit">
            <?php
            $limits = [5, 10, 25, 50];
            $selected_limit = (int)se($_GET, "limit", 10, false);
            foreach ($limits as $lim) {
                $selected = ($selected_limit === $lim) ? "selected" : "";
                echo "<option value='$lim' $selected>$lim</option>";
            }
            ?>
        </select>

        <input type="submit" value="Apply Filters" />
        <!-- Clear Filters button -->
        <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'">Clear Filters</button>
    </form>

    <?php
    $db = getDB();
    $params = [];
    $query = "SELECT id, title, year, rating FROM Movies WHERE 1=1";

    // gs658 - 5/1/25 - Title filter
    $title = trim(se($_GET, "title", "", false));
    if (!empty($title)) {
        $query .= " AND title LIKE :title";
        $params[":title"] = "%$title%";
    }

    // Rating range
    $min_rating = floatval(se($_GET, "min_rating", 0, false));
    $max_rating = floatval(se($_GET, "max_rating", 5, false));
    if ($min_rating > 0) {
        $query .= " AND rating >= :min_rating";
        $params[":min_rating"] = $min_rating;
    }
    if ($max_rating > 0 && $max_rating <= 5) {
        $query .= " AND rating <= :max_rating";
        $params[":max_rating"] = $max_rating;
    }

    // Year range
    $min_year = intval(se($_GET, "min_year", 0, false));
    $max_year = intval(se($_GET, "max_year", 0, false));
    if ($min_year > 0) {
        $query .= " AND year >= :min_year";
        $params[":min_year"] = $min_year;
    }
    if ($max_year > 0) {
        $query .= " AND year <= :max_year";
        $params[":max_year"] = $max_year;
    }

    // Sort
    $allowed_sort = ["title", "year", "rating"];
    $sort_by = se($_GET, "sort_by", "title", false);
    $order = se($_GET, "order", "asc", false);
    if (!in_array($sort_by, $allowed_sort)) {
        $sort_by = "title";
    }
    $order = ($order === "desc") ? "DESC" : "ASC";
    $query .= " ORDER BY $sort_by $order";
    //echo "<pre>" . var_export($_GET, true) . "</pre>";
    // Limit
    $limit = (int)se($_GET, "limit", 10, false);

    if (!in_array($limit, ["5", "10", "25", "50"])) {
        $limit = 50;
    }
    $query .= " LIMIT $limit";

    // Prepare and bind values
    $stmt = $db->prepare($query);
    foreach ($params as $key => &$val) {
        if (is_int($val)) {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        } elseif (is_float($val)) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        } else {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
    }

    // Execute and display
    $results = [];
    try {
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        flash("Error fetching movies: " . $e->getMessage(), "danger");
    }
    // gs658 - 5/1/25 - Generates movie list
    if (!empty($results)) {
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>Title</th><th>Year</th><th>Rating</th><th>Actions</th><th>Watchlist</th></tr>";
        foreach ($results as $movie) {
            // Generates URLs for View, Edit, and Delete actions
            $view_url = "movie-view.php?id=" . $movie["id"];
            $edit_url = "movie-edit.php?id=" . $movie["id"];
            $delete_url = "movie-delete.php?id=" . $movie["id"];

            echo "<tr>";
            echo "<td>" . htmlspecialchars($movie["title"]) . "</td>";
            echo "<td>" . htmlspecialchars($movie["year"]) . "</td>";
            echo "<td>" . htmlspecialchars($movie["rating"]) . "</td>";
            echo "<td><a href='$view_url'>View</a>";
            if (has_role("Admin")) {
                echo " | <a href='$edit_url'>Edit</a> | 
                      <a href='$delete_url' onclick='return confirm(\"Are you sure you want to delete this movie?\")'>Delete</a>";
            }
            echo "</td>";
            // Checks if this movie is already in the user's watchlist
            $in_watchlist = false;
            if (is_logged_in()) {
                $check_stmt = $db->prepare("SELECT id FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
                $check_stmt->execute([":uid" => get_user_id(), ":mid" => $movie["id"]]);
                $in_watchlist = $check_stmt->fetch() !== false;
            }

            echo "<td>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='movie_id' value='" . $movie["id"] . "' />";
                        if ($in_watchlist) {
                            echo "<input type='submit' name='remove_from_watchlist' value='Remove' />";
                        } else {
                            echo "<input type='submit' name='add_to_watchlist' value='Add' />";
                        }
                        echo "</form>
            </td>";

            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><strong>No results available.</strong></p>";
    }
    ?>

</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>