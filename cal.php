<?php

$commonAllow = true;
require_once('common.php');

header("Content-type: text/calendar");
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="examschedule.ics"');

if (0 < count($courses)) {
  print "BEGIN:VCALENDAR\r\nMETHOD:PUBLISH\r\nVERSION:2.0\r\n";
  print "PRODID:-//DENNIS BELLINGER//EXAMS//EN\r\n";
  // print out each selected course
  $stmt = $db->prepare(
    "SELECT ID, Course, Number, Section, StartTime, EndTime, Location, Notes, " .
    "LastUpdated FROM Exams WHERE Term = (SELECT ID FROM Terms WHERE Term_ID " .
    "= ?) AND Course = ? AND Number = ? AND Section = ?");
  foreach ($courses as $selected) {
    $stmt->bindParam(1, $term);
    $stmt->bindParam(2, $selected['discipline']);
    $stmt->bindParam(3, $selected['number']);
    $stmt->bindParam(4, $selected['section']);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $res = $res[0];

    print "BEGIN:VEVENT\r\n";
    print "SUMMARY:${res['Course']} ${res['Number']} Section" .
      " ${res['Section']}\r\n";
    print "DESCRIPTION:Final Exam for ${res['Course']} ${res['Number']}" .
     " Section ${res['Section']}\r\n";
    print "UID:${res['ID']}\r\n";
    print "STATUS:CONFIRMED\r\n";
    print "DTSTART:" . gmdate('Ymd\THis\Z', strtotime($res['StartTime'])) . "\r\n";
    print "DTEND:" . gmdate('Ymd\THis\Z', strtotime($res['EndTime'])) . "\r\n";
    print "LAST-MODIFIED:" .
      gmdate('Ymd\THis', strtotime($res['LastModified'])) . "\r\n";
    print "LOCATION:${res['Location']}\r\n";
    print "END:VEVENT\r\n";
  }
  print "END:VCALENDAR\r\n";
}
