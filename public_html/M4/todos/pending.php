<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
$db = getDB();
// process complete action
if (isset($_POST["id"])) {
    $id = $_POST["id"];
    /*
    Create a query that'll update the respective ToDo marking it complete and setting the date for the completed date field as today.
    Ensure the "id" is utilized using proper PDO named placeholders so that only the one item is updated.
    Add an extra clause to update only if the complete field of the record is not set.
    https://phpdelusions.net/pdo
    */
    /* gs658 - 5/2/25 - The query marks tasks as completed by setting the completed
    field to the current date using NOW(). It also ensures that only tasks that 
    haven’t been marked as completed yet, using completed IS NULL, are updated, 
    preventing any unnecessary updates.
    */
    $query = "UPDATE M4_Todos SET completed = NOW(), is_complete = 1 WHERE id
     = :id AND completed IS NULL";
    $params = [":id" => $id];
    
    try {
        $stmt = $db->prepare($query);
        $r = $stmt->execute($params);
        if ($r) {
            echo "Marked task $id as completed";
        } else {
            echo "Failed to mark task $id as completed";
        }
    } catch (PDOException $e) {
        echo "Error updating task $id; check the logs (terminal)";
        error_log("Update Error: " . var_export($e, true)); // shows in the terminal
    }
}
/* Refer to the HTML table below and build a query that'll select the columns in the same order as the table from the Todo table.
Cross-reference the HTML table columns with what'd most plausibly match the SQL table aside from the notes below.
For the Status part, you'll need to calculate the "days_offset" from the due date, ensure the virtual column matches "days_offset".
For Actions, this isn't part of the query and there's nothing special to select for it.
Filter the results where the todo item is NOT completed and order the results by those due the soonest.
No limit is required.
*/
/* gs658 - 5/2/25 - This query will filter tasks that are not completed using
completed IS NULL and DATEDIFF(due, CURDATE()) to calculate the days_offset.
ORDER BY due ASC will order the tasks by the nearest due date.
*/
$query = "SELECT id, task, due, assigned, DATEDIFF(due, CURDATE()) AS 
days_offset FROM M4_Todos WHERE completed IS NULL ORDER BY due ASC";
$results = [];
try {
    $stmt = $db->prepare($query);
    $r = $stmt->execute();
    if ($r) {
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    echo "Error fetching pending todos; check the logs (terminal)";
    error_log("Select Error: " . var_export($e, true)); // shows in the terminal
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Pending ToDos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <?php foreach ($r as $key => $val): ?>
                            <?php if ($key == "days_offset"): ?>
                                <?php if ($val >= 0): ?>
                                    <td><?php echo "Due in $val day(s)"; ?></td>
                                <?php else: ?>
                                    <td><?php echo "Overdue by " . abs($val) . " day(s)"; ?></td>
                                <?php endif; ?>

                            <?php else: ?>
                                <td><?php echo $val; ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>" />
                                <input type="submit" value="Complete" />
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($results) === 0): ?>
                    <tr>
                        <td colspan="100%">No results</td>

                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>

</html>