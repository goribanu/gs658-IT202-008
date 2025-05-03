<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    /* gs658 - 5/2/25 - This validation ensures the task is not empty and that
    the due date is in the correct format. It also ensures the assigned field
    defaults to "self" when left blank.
    */
    if (empty($task)) {
        echo "Task is required.<br>";
        $is_valid = false;
    }

    $date = DateTime::createFromFormat('Y-m-d', $due);
    if (!$date || $date->format('Y-m-d') !== $due) {
        echo "Due date must be in MM-DD-YYYY format.<br>";
        $is_valid = false;
    }

    if (empty($assigned) || strtolower($assigned) === "none") {
        $assigned = "self";
    }
    // End validations

    
    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo
        */
        /* gs658 - 5/2/25 - The query uses named placeholders to insert user input 
        into corresponding Todo table columns. This not only inserts the data 
        correctly but also protects against SQL injection.
        */
        $query = "INSERT INTO M4_Todos (task, due, assigned) VALUES (:task, :due, :assigned)";
        $params = [
            ":task" => $task,
            ":due" => $due,
            ":assigned" => $assigned
        ];
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);
            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }
        } catch (PDOException $e) {
            // extra credit
            // check if the exception was related to a unique constraint
            // provide an appropriate user-friendly message for this scenario
            // Otherwise show the default message below
             /* gs658 - 5/2/25 - The check will identify if the PDO exception
            was caused by a unique constraint violation, and will display a
            user-friendly message.
            */
            if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                echo "This task already exists.";
            } else {
                echo "There was an error inserting the record. Check the logs.";
                error_log("Insert Error: " . var_export($e, true));
            }
        }
    } else {
        error_log("Creation input wasn't valid");
    }
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Create ToDo </h2>
        <form>
        <div>
            <label for="task">Task</label>
            <input type="text" id="task" name="task" required />
        </div>
        <div>
            <label for="due">Due Date</label>
            <input type="date" id="due" name="due" required />
        </div>
        <div>
            <label for="assigned">Assigned</label>
            <input type="text" id="assigned" name="assigned" value="self" />
        </div>
            <div>
                <input type="submit" />
            </div>
        </form>
    </section>
</body>
</body>

</html>