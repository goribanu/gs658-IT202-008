<?php
require(__DIR__ . "/../../partials/nav.php");

// Get logged-in user's ID
$user_id = get_user_id();
if (!$user_id) {
    flash("You must be logged in to view your watchlist", "danger");
    redirect("login.php");
}

$db = getDB();

// Handle clear watchlist
if (isset($_POST["clear_watchlist"])) {
    $stmt = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid");
    $stmt->execute([":uid" => $user_id]);
    flash("Watchlist cleared successfully.", "info");
}

// Handle remove individual movie
if (isset($_POST["remove_movie"]) && isset($_POST["movie_id"])) {
    $movie_id = (int)$_POST["movie_id"];
    $stmt = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
    $stmt->execute([":uid" => $user_id, ":mid" => $movie_id]);
    flash("Movie removed from watchlist.", "info");
}

// Limit handling
$limit = isset($_GET["limit"]) && in_array($_GET["limit"], ["5", "10", "25", "50"]) ? (int)$_GET["limit"] : 10;

// Fetch watchlist
$total_stmt = $db->prepare("SELECT COUNT(*) FROM Watchlist WHERE user_id = :uid");
$total_stmt->execute([":uid" => $user_id]);
$total_count = $total_stmt->fetchColumn();

$stmt = $db->prepare("
    SELECT Movies.id, Movies.title, Movies.rating, Movies.year
    FROM Watchlist
    JOIN Movies ON Movies.id = Watchlist.movie_id
    WHERE Watchlist.user_id = :uid
    LIMIT :limit
");
$stmt->bindValue(":uid", $user_id, PDO::PARAM_INT);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-container">
    <h1>Your Watchlist</h1>
    <form method="GET" class="mb-3">
        <label for="limit">Show:</label>
        <select name="limit" onchange="this.form.submit()">
            <?php
            $options = [5, 10, 25, 50];
            foreach ($options as $opt) {
                $selected = ($limit == $opt) ? "selected" : "";
                echo "<option value='$opt' $selected>$opt</option>";
            }
            ?>
        </select> entries
    </form>

    <p><strong>Showing <?= count($results) ?> of <?= $total_count ?> movies</strong></p>

    <form method="POST" onsubmit="return confirm('Are you sure you want to clear your entire watchlist?');">
        <input type="submit" name="clear_watchlist" value="Clear Watchlist" class="btn btn-danger mb-2" />
    </form>

    <?php if (!empty($results)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Rating</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $movie): ?>
                    <tr>
                        <td><a href="movie-view.php?id=<?= $movie['id'] ?>"><?= htmlspecialchars($movie["title"]) ?></a></td>
                        <td><?= htmlspecialchars($movie["year"]) ?></td>
                        <td><?= htmlspecialchars($movie["rating"]) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Remove this movie?');" style="display:inline;">
                                <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>" />
                                <input type="submit" name="remove_movie" value="Remove" class="btn btn-sm btn-warning" />
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><strong>No movies in your watchlist yet.</strong></p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>