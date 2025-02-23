<?php

require_once "base.php";

$ucid = "gs658"; // <-- set your ucid


$array1 = [42, -17, 89, -256, 1024, -4096, 50000, -123456];
$array2 = [3.14159265358979, -2.718281828459, 1.61803398875, -0.5772156649, 0.0000001, -1000000.0];
$array3 = [1.1, -2.2, 3.3, -4.4, 5.5, -6.6, 7.7, -8.8];
$array4 = ["123", "-456", "789.01", "-234.56", "0.00001", "-99999999"];
$array5 = [-1, 1, 2.0, -2.0, "3", "-3.0"];

function bePositive($arr, $arrayNumber)
{
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoMixed($arr, $arrayNumber);

    $output = array_fill(0, count($arr), null); // Initialize output array
    // gs658 2-22-25 : Challenge 1 - In order to solve this problem, I will
    // use the abs() function in order to take the positive value of each
    // element. For the strings, I will need to convert them into integers
    // first, then take the abs(). Challenge 2 - I'll convert each element
    // back to its og data type by checking the original value to see if
    // it was an int, float, or a string. Based on these conditions, I will
    // convert it back to the og data type and assign it back to its og
    // position in the array.

    foreach ($arr as $key => $value) {
        if (is_string($value)) {
            if (is_numeric($value)) {
                if (strpos($value, '.') !== false) {
                    $value = (float)$value;
                } else {
                    $value = (int)$value;
                }
            }
        }
        $posValue = abs($value);
        if (is_int($value)) {
            $output[$key] = intval($posValue);
        } elseif (is_float($value)) {
            $output[$key] = floatval($posValue);
        } elseif (is_string($arr[$key])) {
                $output[$key] = (string)$output[$key];
        }
    }

    echo "<p>Output: </p>";
    printOutputWithType($output);
    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 3);
bePositive($array1, 1);
bePositive($array2, 2);
bePositive($array3, 3);
bePositive($array4, 4);
bePositive($array5, 5);
printFooter($ucid, 3);