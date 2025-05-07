<?php
require(__DIR__ . "/../../partials/nav.php");

$db = getDB();
$user_id = isset($_GET["id"]) ? (int)$_GET["id"] : get_user_id();

if (!$user_id) {
    flash("Invalid user ID", "danger");
    redirect("login.php");
}

// Only allow access if user is admin or viewing their own profile
if (!has_role("Admin") && $user_id !== get_user_id()) {
    flash("You don't have permission to view this profile", "danger");
    redirect("home.php");
}

// Handle admin adding to watchlist
if (has_role("Admin") && isset($_POST["add_movie"]) && !empty($_POST["movie_id"])) {
    $movie_id = (int)$_POST["movie_id"];

    // Check if already in watchlist
    $check = $db->prepare("SELECT 1 FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
    $check->execute([":uid" => $user_id, ":mid" => $movie_id]);

    if (!$check->fetch()) {
        $insert = $db->prepare("INSERT INTO Watchlist (user_id, movie_id) VALUES (:uid, :mid)");
        $insert->execute([":uid" => $user_id, ":mid" => $movie_id]);
        flash("Movie added to watchlist", "success");
    } else {
        flash("Movie is already in watchlist", "warning");
    }
}

// Handle admin removing from watchlist
if (has_role("Admin") && isset($_POST["remove_movie"]) && !empty($_POST["movie_id"])) {
    $movie_id = (int)$_POST["movie_id"];
    $delete = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
    $delete->execute([":uid" => $user_id, ":mid" => $movie_id]);
    flash("Movie removed from watchlist", "info");
}

// Fetch user info
$stmt = $db->prepare("SELECT username, email FROM Users WHERE id = :id");
$stmt->execute([":id" => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's watchlist
$watchlist = [];
$stmt = $db->prepare("
    SELECT Movies.id, Movies.title, Movies.year, Movies.rating
    FROM Watchlist
    JOIN Movies ON Watchlist.movie_id = Movies.id
    WHERE Watchlist.user_id = :uid
    ORDER BY Movies.title ASC
");
$stmt->execute([":uid" => $user_id]);
$watchlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all movies (for admin to add to watchlist)
$all_movies = [];
if (has_role("Admin")) {
    $stmt = $db->prepare("SELECT id, title FROM Movies ORDER BY title ASC");
    $stmt->execute();
    $all_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <h1>User Profile</h1>

    <?php if ($user): ?>
        <p><strong>Username:</strong> <?= htmlspecialchars($user["username"]) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user["email"]) ?></p>

        <h3>Watchlist</h3>
        <?php if (!empty($watchlist)): ?>
            <table border="1" cellpadding="8">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Year</th>
                        <th>Rating</th>
                        <?php if (has_role("Admin")): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($watchlist as $movie): ?>
                        <tr>
                            <td><?= htmlspecialchars($movie["title"]) ?></td>
                            <td><?= htmlspecialchars($movie["year"]) ?></td>
                            <td><?= htmlspecialchars($movie["rating"]) ?></td>
                            <?php if (has_role("Admin")): ?>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Remove this movie from the user\'s watchlist?');">
                                        <input type="hidden" name="movie_id" value="<?= $movie["id"] ?>">
                                        <input type="submit" name="remove_movie" value="Remove">
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><em>This user has no movies in their watchlist.</em></p>
        <?php endif; ?>

        <?php if (has_role("Admin")): ?>
            <h4>Add Movie to Watchlist</h4>
            <form method="POST">
                <select name="movie_id" required>
                    <option value="" disabled selected>Select a movie</option>
                    <?php foreach ($all_movies as $movie): ?>
                        <option value="<?= $movie["id"] ?>"><?= htmlspecialchars($movie["title"]) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="add_movie" value="Add">
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p><strong>User not found.</strong></p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>