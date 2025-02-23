<?php

require_once "base.php";

$ucid = "gs658"; // <-- set your ucid


$array1 = ["hello world!", "php programming", "special@#$%^&characters", "numbers 123 456", "mIxEd CaSe InPut!"];
$array2 = ["hello world", "php programming", "this is a title case test", "capitalize every word", "mixEd CASE input"];
$array3 = ["  hello   world  ", "php    programming  ", "  extra    spaces  between   words   ",
    "      leading and trailing spaces      ", "multiple      spaces"];
$array4 = ["hello world", "php programming", "short", "a", "even"];


function transformText($arr, $arrayNumber) {
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoBasic($arr, $arrayNumber);

    // Challenge 1: Remove non-alphanumeric characters except spaces
    // Challenge 2: Convert text to Title Case
    // Challenge 3: Trim leading/trailing spaces and remove duplicate spaces
    // Result 1-3: Assign final phrase to `$placeholderForModifiedPhrase`

    $placeholderForModifiedPhrase = "";
    $placeholderForMiddleCharacters = "";
    // gs658 2-22-25 : Challenge 1 - In order to solve this problem, I will
    // have to search for and remove all characters that are not letters,
    // numbers, or spaces. I'll likely do this using a built in function.
    // Challenge 2 - I will first use strtolower() to make all the text
    // lowercase, then I will use ucwords() to make the first letter of each 
    // word uppercase. Challenge 3 - I will search for all the leading/trailing
    // and dupe spaces and remove them through the same built in function I 
    // am gonna use for challenge 1.

    foreach ($arr as $index => $text) {
        $text = preg_replace("/[^a-zA-Z0-9\s]/", "", $text);
        $text = ucwords(strtolower($text));
        $text = preg_replace('/\s+/', ' ', trim($text));
        $placeholderForModifiedPhrase = $text;
        printStringTransformations($index, $placeholderForModifiedPhrase, $placeholderForMiddleCharacters);
    }

    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 4);
transformText($array1, 1);
transformText($array2, 2);
transformText($array3, 3);
transformText($array4, 4);
printFooter($ucid, 4);

?>