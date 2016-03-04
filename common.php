<?php

if (!isset($commonAllow) || !$commonAllow) {
  die();
}

require('passwords.php');

$dsn="mysql:host=$hostname;dbname=$database";
$db = new PDO($dsn, $username, $password);

if (isset($_GET['courses'])) {
  $fname = $_GET['courses'];
  $courses = str_replace("_", "/", $fname);
  $courses = str_replace("-", "+", $courses);
  $courses = base64_decode($courses);
  $courses = gzuncompress($courses);
  $courseString = $courses;
  $courses = json_decode($courses, true);
  if (isset($courses['course'])) {
    $course = $courses['course'];
  } else {
    $course = array();
  }
  if (isset($courses['term'])) {
    $term = $courses['term'];
  } else {
    $term = '';
  }
  $courses = $courses['courses'];
} else {
  $courses = array();
  $course = array();
}

if (isset($course['discipline']) && isset($course['number']) &&
  isset($course['section']) && isset($course['term'])) {
  $courses[count($courses)] = $course;
  $course = array();
  sort($courses);
  header("Location: /" . encode($courses, $course, $term));
}

if ('' != $term) {
  $stmt = $db->prepare(
    "SELECT Name FROM Terms WHERE Term_ID = ?");
  $stmt->bindParam(1, $term);
  $stmt->execute();
  $res = $stmt->fetch(PDO::FETCH_ASSOC);
  $termString = $term . ' (' . $res['Name'] . ')';
} else {
  $termString = '';
}

function encode($courses, $course, $term) {
  $courses = array(
    "courses" => $courses,
    "course" => $course,
    "term" => $term);
  $jsonCourses = json_encode($courses);
  $jsonCourses = gzcompress($jsonCourses);
  $jsonCourses = base64_encode($jsonCourses);
  $jsonCourses = str_replace("/", "_", $jsonCourses);
  $jsonCourses = str_replace("+", "-", $jsonCourses);
  return $jsonCourses;
}
