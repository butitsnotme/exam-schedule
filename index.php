<?php

$commonAllow = true;
$forwardable = true;
require_once('common.php');

?>
<!DOCTYPE html>
<html>
<head>
<title>Select Courses</title>
</head>
<body>
<h1>Select Final Exams</h1>
<p>This site allows <a href="https://uwaterloo.ca/">University of Waterloo</a>
students to select their courses for a given term to create a customized
exam calendar suitable for import into <a href="https://calendar.google.com"
>Google Calendar</a> or desktop calendar software (it should also work with
iCloud/iPhone/Mac though this has not been tested).

<p>The University of Waterloo requests that I inform users of this website
that the UWaterloo Open Data API (where this site gets its information) is not
considered an authoritative data source and that students should consult the
authoritative data source to ensure accuracy. The authoritative source for the
data presented here is the Registrar's office, who publishes the exam schedule
as a PDF. The official copy of the exam schedule may be found
<a href="https://uwaterloo.ca/registrar/final-examinations/exam-schedule"
>here</a>.

<?php

//print "<h2>Debugging</h2>";
//var_dump($courseString);
//print "</br>";
//var_dump($courses);
//print "</br>";
//print "<a href=\"/" . encode($courses, $course, '', $term) . "\">No Token</a>";

if (0 < count($courses)) {
  print "<h2>Selected Exams</h2>\n";
  print "<table>\n";
  print "  <tr>\n";
  print "    <th>" . 'Term' . "</th>\n";
  print "    <th>" . 'Course' . "</th>\n";
  print "    <th>" . 'Number' . "</th>\n";
  print "    <th>" . 'Section' . "</th>\n";
  print "    <th>" . 'Start Time' . "</th>\n";
  print "    <th>" . 'End Time' . "</th>\n";
  print "    <th>" . 'Location' . "</th>\n";
  print "    <th>" . 'Notes' . "</th>\n";
  print "    <th>" . 'Actions' . "</th>\n";
  print "  </tr>\n";

  // print out each selected course
  $stmt = $db->prepare(
    "SELECT Course, Number, Section, StartTime, EndTime, Location, Notes " .
    "FROM Exams WHERE Term = (SELECT ID FROM Terms WHERE Term_ID = ?) " .
    "AND Course = ? AND Number = ? AND Section = ?");
  foreach ($courses as $key => $selected) {
    $stmt->bindParam(1, $term);
    $stmt->bindParam(2, $selected['discipline']);
    $stmt->bindParam(3, $selected['number']);
    $stmt->bindParam(4, $selected['section']);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $res = $res[0];

    $missingCourse = $courses;
    unset ($missingCourse[$key]);
    $goneLink = "<a href=\"/" . encode($missingCourse, $course, $token) .
      "\">remove</a>";

    print "  <tr>\n";
    print "    <td>" . $termString . "</td>\n";
    print "    <td>" . $res['Course'] . "</td>\n";
    print "    <td>" . $res['Number'] . "</td>\n";
    print "    <td>" . $res['Section'] . "</td>\n";
    print "    <td>" . $res['StartTime'] . "</td>\n";
    print "    <td>" . $res['EndTime'] . "</td>\n";
    print "    <td>" . $res['Location'] . "</td>\n";
    print "    <td>" . $res['Notes'] . "</td>\n";
    print "    <td>" . $goneLink . "</td>\n";
    print "  </tr>\n";
  }
  print "</table>";
  print "<a href=\"/" .
    encode(array(), array(), $token) .
    ".ics\">Calendar Link</a>\n";
}
  
if ('' == $term) {
  $stmt = $db->prepare(
    "SELECT Term_ID, Name FROM Terms ORDER BY Term_ID ASC");
  $stmt->execute();
  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  print "<h2>Select Term</h2>\n";
  print "  <table>\n";
  print "    <tr>\n";
  $count = 0;
  foreach ($res as $item) {
    $count++;
    if ($count > 10) {
      $count = 1;
      print "  </tr>\n  <tr>\n";
    }
    $term = $item['Term_ID'];
    print "      <td><a href=\"/".
          encode(array(), array(), $token, $term) .
          "\">${item["Name"]}</a></td>\n";
  }
  print "    </tr>\n  </table>\n";
} else if (!isset($course['discipline'])) {
  $stmt = $db->prepare(
    "SELECT Course FROM Exams WHERE Term = (SELECT ID FROM Terms WHERE " .
    "Term_ID = ?) GROUP BY Course ORDER BY Course ASC");
  $stmt->bindParam(1, $term);
  $stmt->execute();
  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  print "<h2>Select Another Course</h2>\n";
  print "<p>Term: $termString\n";
  print "  <table>\n";
  print "    <tr>\n";
  $count = 0;
  foreach ($res as $item) {
    $count++;
    if ($count > 10) {
      $count = 1;
      print "  </tr>\n  <tr>\n";
    }
    $course['discipline'] = $item['Course'];
    print "      <td><a href=\"/" .
          encode(array(), $course, $token) .
          "\">${item["Course"]}</a></td>\n";
  }
  print "    </tr>\n  </table>\n";

} else if (!isset($course['number'])) {
  $stmt = $db->prepare(
    "SELECT Number FROM Exams WHERE Term = (SELECT ID FROM Terms WHERE " .
    "Term_ID = ?) AND Course = ? GROUP BY Number ORDER BY Number ASC");
  $stmt->bindParam(1, $term);
  $stmt->bindParam(2, $course['discipline']);
  $stmt->execute();
  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  print "<h2>Select Course Number</h2>\n";
  print "<p>Term: $termString, Course: ${course['discipline']}\n";
  print "  <table>\n";
  print "    <tr>\n";
  $count = 0;
  foreach($res as $item) {
    $count++;
    if ($count > 10) {
      $count = 1;
      print "  </tr>\n  <tr>\n";
    }
    $course['number'] = $item['Number'];
    print "      <td><a href=\"/" .
          encode(array(), $course, $token) .
          "\">${item['Number']}</a></td>";
  }
  print "    </tr>\n  </table>\n";
} else if (!isset($course['section'])) {
  $stmt = $db->prepare(
    "SELECT Section FROM Exams WHERE Term = (SELECT ID FROM Terms WHERE " .
    "Term_ID = ?) AND Course = ? AND Number = ? GROUP BY Section ORDER " .
    "BY Section ASC");
  $stmt->bindParam(1, $term);
  $stmt->bindParam(2, $course['discipline']);
  $stmt->bindParam(3, $course['number']);
  $stmt->execute();
  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  print "<h2>Select Course Section</h2>\n";
  print "<p>Term: $termString, Course: ${course['discipline']}, " .
        "Number: ${course['number']}\n";
  print "  <table>\n";
  print "    <tr>\n";
  $count = 0;
  foreach($res as $item) {
    $count++;
    if ($count > 10) {
      $count = 1;
      print "  </tr>\n  <tr>\n";
    }
    $course['section'] = $item['Section'];
    print "      <td><a href=\"/" .
          encode(array(), $course, $token) .
          "\">${item['Section']}</a></td>";
  }
  print "    </tr>\n  </table>\n";
}

?>
<h2>Instructions</h2>
<p>To import into Google Calendar in such a way that changes to the schedule
are automatically reflected on your calendar, you must add it as a URL. To do
this, first build your custom calendar using the links below. When you have
all of the exams listed in the table at the top, right click on the 'Calendar
Link' link and select 'Copy link location' or similar. Open your Google Calendar.
On the left side is a bar listing your calendars, in it there should be a
heading labelled 'Other Calendars', click the little downward pointing arrow
on the right of this heading. Select 'Add by URL' in the popup menu and paste
the link into the box that appears.

<p>To download a '.ics' or 'iCalendar' file, simply click the 'Calendar Link'
link.

<p>Please note: If you need to add or remove exams from your schedule, you will
need to rebuild your calendar and either redownload the file or re-add it to 
Google using the steps above.

<p>Tracking tokens are used to collect statistics on the usage of this website.
The only data stored in relation to them is the time they are created, the time
they were last seen, the number of times they have been seen, and the courses
that have been selected for them. No information about who or from where they
are accessed is collected. Here is your tracking data:
<ul>
<li>Tracking Token: <?php echo $token; ?></li>
<li>Creation Timestamp: <?php echo $info['CreationTime']; ?></li>
<li>Last Seen: <?php echo $info['LastAccessed']; ?></li>
<li>Number of time seen: <?php echo $info['NumberOfAccesses']; ?></li>
</ul>
</body>
</html>
