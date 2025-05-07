<?php
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "danger");
    redirect("login.php");
}

$db = getDB();

// Handle removal from watchlist
if (isset($_POST["remove_watchlist"]) && isset($_POST["user_id"]) && isset($_POST["movie_title"])) {
    $user_id = (int)$_POST["user_id"];
    $movie_title = $_POST["movie_title"];

    // Get movie_id based on title (assuming titles are unique, otherwise use movie_id in the form)
    $stmt = $db->prepare("SELECT id FROM Movies WHERE title = :title");
    $stmt->execute([":title" => $movie_title]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($movie && isset($movie["id"])) {
        $movie_id = (int)$movie["id"];
        $delete = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
        $delete->execute([":uid" => $user_id, ":mid" => $movie_id]);
        flash("Removed movie from user's watchlist", "success");
    } else {
        flash("Movie not found", "danger");
    }
}

$results = [];

try {
    $stmt = $db->prepare("
        SELECT 
            Users.id as user_id,
            Users.username,
            Movies.title,
            Movies.year,
            Movies.rating
        FROM Watchlist
        JOIN Users ON Watchlist.user_id = Users.id
        JOIN Movies ON Watchlist.movie_id = Movies.id
        ORDER BY Users.username ASC, Movies.title ASC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    flash("Error loading admin watchlist: " . $e->getMessage(), "danger");
}
?>

<div class="container-fluid">
    <h1>Admin View: User Watchlists</h1>

    <?php
    if (!empty($results)) {
        $grouped = [];
        foreach ($results as $row) {
            $user_id = $row["user_id"];
            if (!isset($grouped[$user_id])) {
                $grouped[$user_id] = [
                    "user_id" => $user_id,
                    "username" => $row["username"],
                    "movies" => []
                ];
            }
            $grouped[$user_id]["movies"][] = [
                "title" => $row["title"],
                "year" => $row["year"],
                "rating" => $row["rating"]
            ];
        }

        foreach ($grouped as $uid => $data) {
            $movieCount = count($data["movies"]);
            echo "<h3>User: <a href='view-profile.php?id=" . urlencode($uid) . "'>" . htmlspecialchars($data["username"]) . "</a></h3>";
            echo "(Total Movies: $movieCount)</h3>";
            echo "<table border='1' cellpadding='8'>";
            echo "<thead><tr><th>Title</th><th>Year</th><th>Rating</th><th>Watchlist</th></tr></thead><tbody>";
            foreach ($data["movies"] as $movie) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($movie["title"]) . "</td>";
                echo "<td>" . htmlspecialchars($movie["year"]) . "</td>";
                echo "<td>" . htmlspecialchars($movie["rating"]) . "</td>";
                echo "<td>
                        <form method='POST' onsubmit='return confirm(\"Remove this movie from the user's watchlist?\");'>
                            <input type='hidden' name='user_id' value='" . htmlspecialchars($uid) . "' />
                            <input type='hidden' name='movie_title' value='" . htmlspecialchars($movie["title"]) . "' />
                            <input type='submit' name='remove_watchlist' value='Remove' />
                        </form>
                      </td>";
                echo "</tr>";
            }
            echo "</tbody></table><br>";
        }
    } else {
        echo "<p><strong>No watchlist entries found.</strong></p>";
    }
    ?>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>