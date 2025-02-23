<?php

require_once "base.php";

$ucid = "gs658"; // <-- set your ucid


$array1 = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6];
$array2 = [1.0000001, 1.0000002, 1.0000003, 1.0000004, 1.0000005];
$array3 = [1.0 / 3.0, 2.0 / 3.0, 4.0 / 3.0, 8.0 / 3.0, 8.0 / 3.0];
$array4 = [1e16, 1.0, -1e16, 2.0, -2.0, 1e-16];
$array5 = [M_PI, M_E, sqrt(2), sqrt(3), sqrt(5), log(2), log10(3)];


function sumValues($arr, $arrayNumber)
{
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoDouble($arr, $arrayNumber);

    // gs658 2-22-25 : Challenge 1 - In order to solve this problem, I will
    // create a variable called 'total' and set it equal to the sum of all
    // the elements in the array. I will use the 'array_sum' function to sum
    // all the elements. Challenge 2 - I will create another variable called
    // 'modifiedTotal and set it equal to the sum. I will use the
    // number_format() method to convert the sum to a number with 2 decimal 
    // places.

    $total = 0;
    $total = array_sum($arr);

    $modifiedTotal = "?";
    $modifiedTotal = number_format($total, 2);

    echo "<p>Total Raw Value: {$total}</p>";
    echo "<p>Total Modified Value: {$modifiedTotal}</p>";
    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 2);
sumValues($array1, 1);
sumValues($array2, 2);
sumValues($array3, 3);
sumValues($array4, 4);
sumValues($array5, 5);
printFooter($ucid, 2);