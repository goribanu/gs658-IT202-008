<?php
require(__DIR__ . "/../../partials/nav.php");

// Checks if the ID parameter is provided
if (!isset($_GET['id'])) {
    flash("Movie ID is required.", "danger");
    redirect("movie-list.php"); // Redirect to the movie list page if ID is not provided
    die();
}

$movie_id = $_GET['id']; // retrieves the movie ID from the query string

$db = getDB();

// Fetch the movie data to ensure it exists
$query = "SELECT * FROM Movies WHERE id = :id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $movie_id, PDO::PARAM_INT);
$stmt->execute();
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    flash("Movie not found.", "danger");
    redirect("movie-list.php"); // Redirect if movie is not found
    die();
}

// Delete the movie from the database
$query = "DELETE FROM Movies WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $movie_id, PDO::PARAM_INT);

try {
    $stmt->execute();
    flash("Movie deleted successfully.", "success");
    redirect("movie-list.php"); // Redirect to the movie list after successful deletion
} catch (Exception $e) {
    flash("Error deleting movie: " . $e->getMessage(), "danger");
    redirect("movie-list.php"); // Redirect back to the movie list on error
}
?>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>