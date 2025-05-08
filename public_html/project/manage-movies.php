<?php
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don’t have access to this page", "danger");
    redirect("home.php");
}

$db = getDB();

// Process form to associate/disassociate users and movies
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["associate"])) {
    $selected_users = $_POST["selected_users"] ?? [];
    $selected_movies = $_POST["selected_movies"] ?? [];

    foreach ($selected_users as $user_id) {
        foreach ($selected_movies as $movie_id) {
            $check = $db->prepare("SELECT id FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
            $check->execute([":uid" => $user_id, ":mid" => $movie_id]);

            if ($check->fetch()) {
                // Exists, so remove it
                $delete = $db->prepare("DELETE FROM Watchlist WHERE user_id = :uid AND movie_id = :mid");
                $delete->execute([":uid" => $user_id, ":mid" => $movie_id]);
            } else {
                // Doesn't exist, so insert
                $insert = $db->prepare("INSERT INTO Watchlist (user_id, movie_id) VALUES (:uid, :mid)");
                $insert->execute([":uid" => $user_id, ":mid" => $movie_id]);
            }
        }
    }

    flash("Associations updated successfully", "success");
}

// Filters and limits
$user_search = trim($_GET["user_search"] ?? "");
$movie_search = trim($_GET["movie_search"] ?? "");
$limit = (int)($_GET["limit"] ?? 10);
if (!in_array($limit, [5, 10, 25, 50])) {
    $limit = 10;
}

// Fetch matching users
$user_stmt = $db->prepare("SELECT id, username FROM Users WHERE username LIKE :username LIMIT $limit");
$user_stmt->execute([":username" => "%$user_search%"]);
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch matching movies
$movie_stmt = $db->prepare("SELECT id, title FROM Movies WHERE title LIKE :title LIMIT $limit");
$movie_stmt->execute([":title" => "%$movie_search%"]);
$movies = $movie_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h2>Admin Association Page</h2>
    <form method="GET" class="mb-3">
        <label>Search Username:</label>
        <input type="text" name="user_search" value="<?= se($user_search, null, "") ?>" />
        <label>Search Movie:</label>
        <input type="text" name="movie_search" value="<?= se($movie_search, null, "") ?>" />

        <label>Limit:</label>
        <select name="limit">
            <?php foreach ([5, 10, 25, 50] as $l): ?>
                <option value="<?= $l ?>" <?= $limit == $l ? "selected" : "" ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Filter" class="btn btn-primary" />
    </form>

    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <h4>Users (<?= count($users) ?> results)</h4>
                <?php if ($users): ?>
                    <?php foreach ($users as $u): ?>
                        <div>
                            <input type="checkbox" name="selected_users[]" value="<?= $u["id"] ?>" />
                            <a href="view-profile.php?id=<?= $u["id"] ?>"><?= htmlspecialchars($u["username"]) ?></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No matching users found.</p>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h4>Movies (<?= count($movies) ?> results)</h4>
                <?php if ($movies): ?>
                    <?php foreach ($movies as $m): ?>
                        <div>
                            <input type="checkbox" name="selected_movies[]" value="<?= $m["id"] ?>" />
                            <?= htmlspecialchars($m["title"]) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No matching movies found.</p>
                <?php endif; ?>
            </div>
        </div>
        <br>
        <input type="submit" name="associate" value="Submit Associations" class="btn btn-success" />
    </form>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>