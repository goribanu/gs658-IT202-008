<?php
require(__DIR__ . "/../../partials/nav.php");

// Get logged-in user's ID
$user_id = get_user_id();
if (!$user_id) {
    flash("You must be logged in to view your watchlist", "danger");
    redirect("login.php");
}

// Clear Watchlist option
if (isset($_POST["clear_watchlist"])) {
    $user_id = get_user_id();
    if ($user_id > 0) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid");
        $stmt->execute([":uid" => $user_id]);
        flash("Watchlist cleared successfully.", "info");
    }
}

// Fetch user's watchlist
$db = getDB();
$results = [];
try {
    $stmt = $db->prepare("
        SELECT Movies.title, Movies.rating, Movies.year
        FROM Watchlist
        JOIN Movies ON Movies.id = Watchlist.movie_id
        WHERE Watchlist.user_id = :uid
    ");
    $stmt->execute([":uid" => $user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    flash("Error fetching watchlist: " . $e->getMessage(), "danger");
}
?>

<div class="main-container">
    <h1>Your Watchlist</h1>
    <p><strong>Total Movies:</strong> <?= count($results) ?></p>
    <form method="POST" onsubmit="return confirm('Are you sure you want to clear your entire watchlist?');">
        <input type="submit" name="clear_watchlist" value="Clear Watchlist" class="btn btn-danger" />
    </form>
    <br>

    <?php if (!empty($results)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $movie): ?>
                    <tr>
                        <td><?= htmlspecialchars($movie["title"]) ?></td>
                        <td><?= htmlspecialchars($movie["year"]) ?></td>
                        <td><?= htmlspecialchars($movie["rating"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><strong>No movies in your watchlist yet.</strong></p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>