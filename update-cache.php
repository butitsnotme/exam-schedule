#!/usr/bin/php5

<?php

// Updates the cache with all the exams
$commonAllow = true;
require_once('common.php');

// API Info
$termsURL="https://api.uwaterloo.ca/v2/terms/list.json?key={key}";
$examsURL="https://api.uwaterloo.ca/v2/terms/{term}/examschedule.json?key={key}";

// Get the terms
$termsURL = str_replace("{key}", $apiKey, $termsURL);

$terms = file_get_contents($termsURL);

$terms = json_decode($terms, true);

// Connect to the database
$dsn="mysql:host=$hostname;dbname=$database";
$db = new PDO($dsn, $username, $password);

$stmt = $db->prepare(
  "INSERT INTO Terms (Term_ID, Name, Year) VALUES(?, ?, ?) ON DUPLICATE KEY " .
  "UPDATE Name=?, Year=?");

foreach ($terms["data"]["listings"] as $year => $termlist) {
  foreach($termlist as $term) {
    $stmt->bindParam(1, $term["id"]);
    $stmt->bindParam(2, $term["name"]);
    $stmt->bindParam(3, $year);
    $stmt->bindParam(4, $term["name"]);
    $stmt->bindParam(5, $year);
    $stmt->execute();
  }
}

// Collect all exam information
$stmt = $db->prepare(
  "INSERT INTO Exams (Course, Number, Section, Term, StartTime, EndTime, " .
  "Location, Notes) VALUES(?, ?, ?, (SELECT ID FROM Terms WHERE Term_ID = ?), ".
  "?, ?, ?, ?) ON DUPLICATE KEY UPDATE StartTime=?, EndTime=?, Location=?, " .
  "Notes=?, LastUpdated=NOW()");

$examsURL = str_replace("{key}", $apiKey, $examsURL);

foreach($terms["data"]["listings"] as $termlist) {
  foreach($termlist as $term) {
    $exams = file_get_contents(str_replace("{term}", $term["id"], $examsURL));
    $exams = json_decode($exams, true);

    $count = 0;
    foreach ($exams["data"] as $course) {
      foreach ($course["sections"] as $section) {
        $courseSet = explode(" ", $course["course"]);
        $courseName = $courseSet[0];
        $courseNumber = $courseSet[1];
        $sectionName = $section["section"];
        $startDate = $section["date"] . " " . timeTo24($section["start_time"]);
        $endDate = $section["date"] . " " . timeTo24($section["end_time"]);
        $location = $section["location"];
        $notes = $section["notes"];

        $stmt->bindParam(1, $courseName);
        $stmt->bindParam(2, $courseNumber);
        $stmt->bindParam(3, $sectionName);
        $stmt->bindParam(4, $term["id"]);
        $stmt->bindParam(5, $startDate);
        $stmt->bindParam(6, $endDate);
        $stmt->bindParam(7, $location);
        $stmt->bindParam(8, $notes);
        $stmt->bindParam(9, $startDate);
        $stmt->bindParam(10, $endDate);
        $stmt->bindParam(11, $location);
        $stmt->bindParam(12, $notes);
        $stmt->execute();
      }
    }
  }
}

function timeTo24($time) {
  if ('' == $time) {
    return '';
  }
  return date("H:i", strtotime($time));
}

