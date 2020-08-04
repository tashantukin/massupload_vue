<?php

//The name of the CSV file that will be downloaded by the user.
$fileName = 'example.csv';

//Set the Content-Type and Content-Disposition headers.
// header('Content-Type: application/excel');
// header('Content-Disposition: attachment; filename="' . $fileName . '"');

//A multi-dimensional array containing our CSV data.
$data = array(
    //Our header (optional).
    array("Name", "Registration Date"),
    //Our data
    array("Tom", "2012-01-04"),
    array("Lisa", "2011-09-29"),
    array("Harry", "2013-12-12")
);

//Open up a PHP output stream using the function fopen.
$path = realpath("downloads/example.csv");
$fp = fopen($path, 'w');

//Loop through the array containing our CSV data.
foreach ($data as $row) {
    //fputcsv formats the array into a CSV format.
    //It then writes the result to our output stream.
    fputcsv($fp, $row);
}

//Close the file handle.
fclose($fp);

$file = $path;

header("Content-Description: File Transfer"); 
header("Content-Type: application/octet-stream"); 
header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 

readfile ($file);
exit(); 