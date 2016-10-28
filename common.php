<?php

if (!isset($commonAllow) || !$commonAllow) {
  die();
}

require('passwords.php');

$dsn="mysql:host=$hostname;dbname=$database";
$db = new PDO($dsn, $username, $password);
$forward = false;

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
  if (isset($courses['token']) && ('' != $courses['token'])) {
    $token = $courses['token'];
  } else if (isset($courses['courses']) && !empty($courses['courses'])) {
    $token = hash("sha256", json_encode($courses['courses']), false);
  } else {
    $token = '';
  }
  $courses = $courses['courses'];
} else {
  $courses = array();
  $course = array();
  $term = '';
  $token = '';
}

if ('' == $token) {
  $checkStmt = $db->prepare(
    "SELECT COUNT(*) AS Count FROM Schedule WHERE Token = ?");
  do {
    $token = substr(base64_encode(mt_rand()), 0, 64);
    $checkStmt->bindParam(1, $token);
    $checkStmt->execute();
    $res = $checkStmt->fetch(PDO::FETCH_ASSOC);
  } while(0 != $res['Count']);

  $insStmt = $db->prepare(
    "INSERT INTO Schedules (Token, Courses) VALUES (?, \"[]\")");
  $insStmt->bindParam(1, $token);
  $insStmt->execute();
  $forward = true;
}

$stmt = $db->prepare(
  "SELECT COUNT(*) AS Exists FROM Schedules WHERE Token = ?");
$stmt->bindParam(1, $token);
$stmt->execute();
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (0 == $exists['Exists']) {
  $insStmt = $db->prepare(
    "INSERT INTO Schedules (Token, Courses) VALUES (?, \"[]\")");
  $insStmt->bindParam(1, $token);
  $insStmt->execute();
}

$stmt = $db->prepare(
  "SELECT CreationTime, LastAccessed, NumberOfAccesses FROM Schedules WHERE " .
  "Token = ?");
$stmt->bindParam(1, $token);
$stmt->execute();
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if ('' == $term) {
  $stmt = $db->prepare(
    "SELECT IF(IFNULL(Schedules.Term, -1) < 0, -1, Terms.Term_ID) AS Term_ID " .
    "FROM Schedules LEFT JOIN Terms ON Schedules.Term = Terms.ID WHERE " .
    "Schedules.Token = ?");
  $stmt->bindParam(1, $token);
  $stmt->execute();
  $term = $stmt->fetch(PDO::FETCH_ASSOC);
  if (-1 == $term['Term_ID']) {
    $term = '';
  } else {
    $term = $term['Term_ID'];
  }
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

if (empty($courses)) {
  $stmt = $db->prepare(
    "SELECT Courses FROM Schedules WHERE Token = ?");
  $stmt->bindParam(1, $token);
  $stmt->execute();
  $courses = $stmt->fetch(PDO::FETCH_ASSOC);
  $courses = json_decode($courses['Courses'], true);
} else {
  $forward = true;
}

if (isset($course['discipline']) && isset($course['number']) &&
  isset($course['section'])) {
  $courses[count($courses)] = $course;
  $course = array();
  $forward = true;
}

$stmt = $db->prepare(
  "UPDATE Schedules SET Courses = ?, Term = (SELECT ID FROM Terms WHERE " .
  "Term_ID = ?) WHERE Token = ?");
$stmt->bindParam(1, (0 < count($courses) ? json_encode($courses) : "[]"));
$stmt->bindParam(2, ('' == $term ? null : $term));
$stmt->bindParam(3, $token);
$stmt->execute();

if ($forward && $forwardable) {
  header("Location: /" . encode(array(), $course, $token));
}

function encode($courses, $course, $token, $term = '') {
  $courses = array(
    "courses" => $courses,
    "course" => $course,
    "term" => $term,
    "token" => $token);
  if (empty($courses['courses'])) {
    unset($courses['courses']);
  }
  if ('' == $term) {
    unset($courses['term']);
  }
  if (empty($courses['course'])) {
    unset($courses['course']);
  }
  if ('' == $token) {
    unset($courses['token']);
  }
  $jsonCourses = json_encode($courses);
  $jsonCourses = gzcompress($jsonCourses);
  $jsonCourses = base64_encode($jsonCourses);
  $jsonCourses = str_replace("/", "_", $jsonCourses);
  $jsonCourses = str_replace("+", "-", $jsonCourses);
  return $jsonCourses;
}

