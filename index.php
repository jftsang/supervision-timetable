<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php
/* Read in list of supervisions */
$filename = 'timetable.txt';
$file = fopen($filename,'r');
$supervisions = array();
while($line = fgets($file)) {
    /* Ignore comments */
    if (substr($line,1,1) == '#') 
        continue;

    list($year,$month,$day,$hour,$min,$course,$location,$people) = sscanf($line,
        "%4d-%2d-%2d %2d:%2d %[^;]; %[^;]; %[^\n]\n");
    $time = strtotime("$year-$month-$day $hour:$min");
    $supervisions[] = array('time' => $time, 'course' => $course, 
        'location'=>$location, 'people' => $people );
}

/* Sort supervisions by date and time */
function cmp($a,$b) {
    return $a['time'] > $b['time'];
}
usort($supervisions, 'cmp');

/* Extract the list of people, and their CRSIDs */
foreach($supervisions as &$supervision) {
    $supervision['people'] = explode(', ', $supervision['people']);
}

function timeuntil($time)
{
    $dt=$time-time();
    $hours = $dt / 3600;
    $days = $dt / 3600 / 24;
    $weeks = $dt / 3600 / 24 / 7;
    if (floor($weeks) > 0)
        if (floor($days)%7 > 0)
            return sprintf("%d w, %d d", floor($weeks), floor($days)%7);
        else
            return sprintf("%d w", floor($weeks));
    else if (floor($days) > 0)
        return sprintf("%d d", floor($days));
    else if (floor($hours) > 0)
        return sprintf("%d hr", floor($hours));
    else if (floor($hours) == 0)
        return "(imminent)";
    else if ($dt > -3600)
        return "(ongoing)";
    else
        return "(past)";
}

function daysbetween($time1,$time2) {
    return round(($time2-$time1)/86400);
}

function weeksbetween($time1,$time2) {
    return floor(daysbetween($time1,$time2)/7);
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css"/>
<link rel="stylesheet" type="text/css" href="/style.css"/>
<title>My supervision timetable</title>
</head>
<body>

<div id="content">

<h1>My supervision timetable</h1>

<p style="text-align: center;">Today is <?=date('D d M Y')?>.</p>

<p>Please feel free to swap supervision slots with each other. <a
href="mailto:jmft2@cam.ac.uk">Email me</a> to let me know, and <em>please copy
in everyone else who's involved in that transaction</em>: this helps me know that
you all assent to the swap.</p>

<p>I'm not meant to meddle with supervision <em>pairings</em> &mdash; talk to
your DoS about those.</p>

<!--
<p><strong>Mathematical Biology</strong> Please hand in your work by Sunday 2pm
before each supervision to my Queens' pigeonhole. I will return it the day
before the supervision; please collect it and bring it with you.</p>

<p><strong>Numerical Analysis I</strong> For the first two supervisions, please
hand in your work by Tuesday 5pm before the first two supervisions. I will
return it the day before the supervision; please collect it and bring it with
you.</p>
-->

<table class="timetable">
<thead>
<tr>
    <th style="padding-right: 10px;"> Date</th> 
    <th>Time</th>
    <th style="text-align: center;">Time<br/>from now</th>
    <th style="text-align: left;">Course</th>
    <th style="text-align: left;">Location</th>
    <th style="text-align: left;">People</th>
</tr>
</thead>
<tbody>
<?php
/* Print out list of supervisions */
$mintime = isset($_REQUEST['mintime'])?$_REQUEST['mintime']:(time()-3600*24);
$maxtime = isset($_REQUEST['maxtime'])?$_REQUEST['maxtime']:INF;
foreach($supervisions as &$supervision) {
    /* Ignore if outside the range of interest. */
    if ($supervision['time'] < $mintime || $supervision['time'] > $maxtime) 
        continue;

    /* Otherwise, print the details of this supervision in table form. */
    $cn = str_replace(' ', '', $supervision['course']);
    printf('<tr class="%s">', $cn);
    printf('<td style="text-align: right;">%s</td>', date('D\&\n\b\s\p;d\&\n\b\s\p;M\&\n\b\s\p;Y',$supervision['time']));
    printf('<td>%s</td>', date('H:i',$supervision['time']));
        printf('<td style="text-align: right;">%s</td>', 
            timeuntil($supervision['time'])
        );
    printf('<td style="text-align: left;">%s</td>', $supervision['course']);
    printf('<td style="text-align: left;">%s</td>', $supervision['location']);

    foreach($supervision['people'] as $person) {
        $name = trim(strtok($person, "()"));
        $crsid = trim(strtok("()"));
        // list($name,$crsid) = sscanf($person, "%s (%[^)])");
        $supervision['personstring'] .= 
            sprintf('<a class="%s" href="mailto:%s@cam.ac.uk?subject=%s.">%s</a> ',
                $cn,
                $crsid,
                htmlentities($supervision['course']),
                $name
            ); 
    }
    printf('<td style="text-align: left;">%s</td>', $supervision['personstring']); 
    printf('</tr>');
    printf("\n");
}
?>
</tbody>
</table>

<div style="margin-top: 18px;">
<table style="border: none; margin-left: auto; margin-right: auto;"><tbody>
<tr>
<td><a href="mailto:jmft2@cam.ac.uk">Email me</a></td>
<td><a href="timetable.txt">Raw timetable</a></td>
<td><a href="https://github.com/jftsang/supervision-timetable/">Plagiarise this page</a><br/>
(see also <a href="https://www.youtube.com/watch?v=IL4vWJbwmqM">the song!</a>)</td>
<td><a href="..">Home</a></td>
</tr>
</tbody></table>
</div>
</div>
</body>
</html>
