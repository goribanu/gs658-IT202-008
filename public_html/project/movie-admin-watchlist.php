<?php
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "danger");
    redirect("login.php");
}

$db = getDB();
$results = [];

$movie_title = trim($_GET["movie_title"] ?? "");
$limit = (int)($_GET["limit"] ?? 10);
$valid_limits = [5, 10, 25, 50];
if (!in_array($limit, $valid_limits)) {
    $limit = 10;
}

if (!empty($movie_title)) {
    try {
        $stmt = $db->prepare("
            SELECT 
                Users.id AS user_id,
                Users.username,
                Movies.id AS movie_id,
                Movies.title,
                Movies.year,
                Movies.rating
            FROM Watchlist
            JOIN Users ON Watchlist.user_id = Users.id
            JOIN Movies ON Watchlist.movie_id = Movies.id
            WHERE Movies.title LIKE :title
            ORDER BY Movies.title ASC, Users.username ASC
            LIMIT :limit
        ");
        $stmt->bindValue(":title", "%$movie_title%", PDO::PARAM_STR);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        flash("Error searching watchlist: " . $e->getMessage(), "danger");
    }
}
?>

<div class="container-fluid">
    <h1>Admin View: Movie Watchlists</h1>

    <!-- Search + Filter Form -->
    <form method="GET" class="mb-3">
        <label for="movie_title">Movie Title:</label>
        <input type="text" name="movie_title" value="<?= htmlspecialchars($movie_title) ?>" />

        <label for="limit">User Limit:</label>
        <select name="limit">
            <?php foreach ($valid_limits as $opt): ?>
                <option value="<?= $opt ?>" <?= $limit === $opt ? "selected" : "" ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Filter" class="btn btn-primary btn-sm" />
    </form>

    <?php if (!empty($results)): ?>
        <?php
        $grouped = [];
        foreach ($results as $row) {
            $movie_id = $row["movie_id"];
            if (!isset($grouped[$movie_id])) {
                $grouped[$movie_id] = [
                    "title" => $row["title"],
                    "year" => $row["year"],
                    "rating" => $row["rating"],
                    "users" => []
                ];
            }
            $grouped[$movie_id]["users"][] = [
                "user_id" => $row["user_id"],
                "username" => $row["username"]
            ];
        }

        foreach ($grouped as $movie_id => $movie) {
            $userCount = count($movie["users"]);
            echo "<h3>Movie: " . htmlspecialchars($movie["title"]) . " ({$movie["year"]})</h3>";
            echo "<p>Rating: " . htmlspecialchars($movie["rating"]) . " | Users in Watchlist: $userCount</p>";
            echo "<table border='1' cellpadding='8'>";
            echo "<thead><tr><th>Username</th><th>Actions</th></tr></thead><tbody>";
            foreach ($movie["users"] as $user) {
                echo "<tr>";
                echo "<td><a href='view-profile.php?id=" . urlencode($user["user_id"]) . "'>" . htmlspecialchars($user["username"]) . "</a></td>";
                echo "<td>
                        <form method='POST' onsubmit='return confirm(\"Remove this user from the movie's watchlist?\");'>
                            <input type='hidden' name='user_id' value='" . htmlspecialchars($user["user_id"]) . "' />
                            <input type='hidden' name='movie_title' value='" . htmlspecialchars($movie["title"]) . "' />
                            <input type='submit' name='remove_watchlist' value='Remove' />
                        </form>
                      </td>";
                echo "</tr>";
            }
            echo "</tbody></table><br>";
        }
        ?>
    <?php elseif (!empty($movie_title)): ?>
        <p><strong>No users found with that movie in their watchlist.</strong></p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>