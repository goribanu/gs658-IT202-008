<?php
require(__DIR__ . "/../../partials/nav.php");

// Check if the movie id is provided
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$movie_id) {
    flash("Movie ID is required", "danger");
    redirect("movie-list.php");
    exit;
}

$db = getDB();
$query = "SELECT * FROM Movies WHERE id = :id";
$params = [":id" => $movie_id];

// Prepare and execute the query
$stmt = $db->prepare($query);
$stmt->bindValue(":id", $movie_id, PDO::PARAM_INT);

try {
    $stmt->execute();
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) {
        flash("Movie not found", "danger");
        redirect("movie-list.php");
        exit;
    }
} catch (Exception $e) {
    flash("Error fetching movie details: " . $e->getMessage(), "danger");
    redirect("movie-list.php");
    exit;
}
?>

<div class="container-fluid">
    <h1>Movie Details</h1>

    <p><strong>Title:</strong> <?= htmlspecialchars($movie['title']) ?></p>
    <p><strong>Year:</strong> <?= htmlspecialchars($movie['year']) ?></p>
    <p><strong>Rating:</strong> <?= htmlspecialchars($movie['rating']) ?></p>
    <p><strong>Plot:</strong> <?= htmlspecialchars($movie["plot"] ?? "Plot not available") ?></p>

    <!-- Action buttons to go back -->
    <a href="movie-list.php" class="btn btn-secondary">Back to Movie List</a>

</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>