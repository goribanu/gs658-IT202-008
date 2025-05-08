<?php
require(__DIR__ . "/../../partials/nav.php");

// Check if the ID parameter is provided
if (!isset($_GET['id'])) {
    flash("Movie ID is required.", "danger");
    redirect("movies_list.php"); // Redirect to the movie list page if ID is not provided
    die();
}

$movie_id = $_GET['id'];

$db = getDB();

// Fetch the current movie data from the database
$query = "SELECT * FROM Movies WHERE id = :id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $movie_id, PDO::PARAM_INT);
$stmt->execute();
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    flash("Movie not found.", "danger");
    redirect("movies_list.php"); // Redirect if movie is not found
    die();
}

// Handling form submission for updating the movie details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = intval($_POST['year']);
    $rating = floatval($_POST['rating']);

    // Validate inputs
    if (empty($title) || empty($year) || empty($rating)) {
        flash("Title, Year, and Rating are required fields.", "danger");
    } else {
        // Update the movie in the database
        $update_query = "UPDATE Movies SET title = :title, year = :year, rating = :rating WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $update_stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $update_stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
        $update_stmt->bindValue(':id', $movie_id, PDO::PARAM_INT);

        try {
            $update_stmt->execute();
            flash("Movie updated successfully.", "success");
            redirect("movie-view.php?id=$movie_id"); // Redirect to the view page after successful update
        } catch (Exception $e) {
            flash("Error updating movie: " . $e->getMessage(), "danger");
        }
    }
}
?>

<div class="container-fluid">
    <h1>Edit Movie</h1>

    <!-- Movie edit form -->
    <form method="POST">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" class="form-control" value="<?= se($movie, 'title', '') ?>" required />
        </div>

        <div class="form-group">
            <label for="year">Year:</label>
            <input type="number" name="year" id="year" class="form-control" value="<?= se($movie, 'year', '') ?>" required />
        </div>

        <div class="form-group">
            <label for="rating">Rating:</label>
            <input type="number" name="rating" id="rating" class="form-control" min="0" max="5" step="0.1" value="<?= se($movie, 'rating', '') ?>" required />
        </div>

        <button type="submit" class="btn btn-primary">Update Movie</button>
        <a href="movie-view.php?id=<?= $movie['id'] ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>