<?php
// This file is part of Moodle - http://moodle.org/
//
// free: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for external examines to review assignments
 *
 * @package    report_ee
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');
// Get all first sitting assignments
function get_assignments($course){
  global $DB, $USER, $COURSE;

  $assignments = $DB->get_records_sql('SELECT a.id, a.name, cm.idnumber
          FROM {assign} a
          JOIN {course_modules} cm ON cm.instance = a.id
          JOIN {modules} m ON m.id = cm.module AND m.name = "assign"
          JOIN {local_quercus_tasks_sittings} s ON s.assign = a.id
          WHERE a.course = ?
          AND cm.idnumber!= ""
          AND s.sitting_desc = "FIRST_SITTING"', array($course));

  return $assignments;
}


function get_course_fullname($course){
  global $DB, $USER;
    $coursefullname = $DB->get_field_select("course", "fullname", "id=$course");

  return $coursefullname;
}

function save_form_data($formdata){
  global $DB, $USER;
  $course = $formdata->course;
  // Check to see if record exists in ee table for course .
  $reportrecord =   $DB->get_record('report_ee',(['course'=>$course]), '*');
  if(!$reportrecord){
    $record = new stdClass();
    $record->course = $course;
    $record->comments = $formdata->comments;
    if($formdata->locked == 1){
      $date = new DateTime("now", core_date::get_user_timezone_object());
      $record->locked = $date->getTimestamp();
    }else{
      $record->locked = $formdata->locked;
    }
    $date = new DateTime("now", core_date::get_user_timezone_object());
    $record->timecreated = $date->getTimestamp();
    $reportrecord = $DB->insert_record('report_ee', $record, true);
  }

  // If form record exists, get id and add assignments to ee_assign table (add record to assign table for easy joining)
  $record = new stdClass();
  $record->report = $reportrecord->id;
  $record->user = $USER->id;

  $record2 = new stdClass();
  $record2->id = $reportrecord->id;

  foreach($formdata as $data=>$d){
    $arr = explode("_", $data);
    if($arr[0] == 'assign'){ // If this is an assignment value
      $record->assign = $arr[1];
      // Get the assign id and  value
      if($arr[1] == $record->assign){
        if($arr[2] == 'sample'){
          $record->sample = $d;
        }elseif($arr[2] == 'level'){
          $record->level = $d;
        }elseif($arr[2] == 'national'){ // When we get here we need to process the record
          $record->national = $d;
          $assignrecord =  $DB->get_record('report_ee_assign',(['report'=>$reportrecord->id, 'assign'=>$record->assign]), '*');
          if(!$assignrecord){
            $DB->insert_record('report_ee_assign', $record, true);
          }else{
            $record->id = $assignrecord->id;
            $DB->update_record('report_ee_assign', $record, false);
          }
        }
      }
      $assign = $arr[1];
    }else{
      if($data == 'comments'){
        $record2->comments = $d;
      }elseif ($data == 'locked') {
        if($d == 1){
          $date = new DateTime("now", core_date::get_user_timezone_object());
          $record2->locked = $date->getTimestamp();
        }else{
          $record2->locked = $d;
        }
      }
    }
  }
  $date = new DateTime("now", core_date::get_user_timezone_object());
  $record2->timemodified = $date->getTimestamp();
  $DB->update_record('report_ee', $record2, false);
}

function get_report_data($course){
  global $DB;
  $sql = "SELECT a.*, r.course, r.comments,
          CONCAT(u.firstname, ' ', u.lastname) username, r.locked,
          r.timecreated, r.timemodified
          FROM {report_ee} r
          JOIN {report_ee_assign} a ON a.report = r.id
          JOIN {user} u ON u.id = a.user
          WHERE r.course = ?";
  $data = $DB->get_records_sql($sql, array($course));

  return $data;
}

// Get existing data to populate the form
function process_data($data){
  $assign = 0;
  $username = null;
  $setdata = new stdClass();

  foreach($data as $key=>$val){
    foreach($val as $k=>$v){
      if($k === 'assign'){
        $assign = $v;
      }
      if($k == 'sample'){
        $sample = 'assign_'. $assign .'_sample';
        $setdata->{$sample} = $v;
      }
      if($k == 'level'){
        $level = 'assign_'. $assign .'_level';
        $setdata->{$level} = $v;
      }
      if($k == 'national'){
        $national = 'assign_'. $assign .'_national';
        $setdata->{$national} = $v;
      }
      if($k == 'comments'){
        $setdata->comments = $v;
      }
      if($k == 'username'){
        $username = $v;
      }
      if($k == 'locked'){
        if($v == 0){
          $setdata->locked = $v;
        }else{
          $setdata->locked = 1;
          $date = new DateTime();
          $date->setTimestamp(intval($v));
          $date = userdate($date->getTimestamp());

          $setdata->lockedby = get_string('lockedbydata', 'report_ee', ['username'=>$username, 'date'=>$date]);
        }
      }
    }
  }

  return($setdata);
}

function get_module_leader_emails(){
  global $DB, $COURSE;
  $moduleleaders = $DB->get_record_sql("SELECT GROUP_CONCAT(u.email SEPARATOR ',') emailto
                                        FROM {user} u
                                        INNER JOIN {role_assignments} ra ON ra.userid = u.id
                                        INNER JOIN {context} ct ON ct.id = ra.contextid
                                        INNER JOIN {course} c ON c.id = ct.instanceid
                                        INNER JOIN {role} r ON r.id = ra.roleid
                                        WHERE r.shortname = ?
                                        AND c.id = ?",
                                        array(get_config('report_ee', 'moduleleadershortname'), $COURSE->id));
  return $moduleleaders;
}

function get_external_examiner(){
  global $DB, $COURSE;
  $externalexaminer = $DB->get_record_sql("SELECT CONCAT(u.firstname, ' ', u.lastname) name
                                        FROM {user} u
                                        INNER JOIN {role_assignments} ra ON ra.userid = u.id
                                        INNER JOIN {context} ct ON ct.id = ra.contextid
                                        INNER JOIN {course} c ON c.id = ct.instanceid
                                        INNER JOIN {role} r ON r.id = ra.roleid
                                        WHERE r.shortname = ?
                                        AND c.id = ?",
                                        array(get_config('report_ee', 'externalexaminershortname'), $COURSE->id));
    return $externalexaminer;
}

function send_emails($formdata){
  global $DB, $COURSE, $USER, $CFG;
  $assign = 0;
  $assignmessage = "";
  $actionrequired = "";

  foreach($formdata as $data=>$d){
    $arr = explode("_", $data);
    if($arr[0] == 'assign'){ // If this is an assignment value
      if($arr[1] !== $assign){
        // Get assign name
        $assignment = $DB->get_record('assign', array('id'=>$arr[1]));
        $assignmessage .= "<h4>Assignment - " . $assignment->name . "</h4>";
      }

      switch ($d) {
          case 1:
              $assignmessage .= "<p>" . ucfirst($arr[2]) . " - Yes</p>";
              $subject = '';
              break;
          case 2:
              $assignmessage .= '<p style="font-family: OpenSans, "Trebuchet MS";color:red;font-weight: bold;">' . ucfirst($arr[2]) . " - No </p>";
              $actionrequired = get_string('actionrequired', 'report_ee');
              break;
      }

      $assign = $arr[1];
    }
  }

  $to = get_module_leader_emails()->emailto . ',' . get_config('report_ee', 'studentregemail');
	$subject = $actionrequired . get_string('subject', 'report_ee', $COURSE->shortname);
	$headers = "From: " . $CFG->noreplyaddress . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $externalexaminer = get_external_examiner()->name;
  $messagebody = "<p>" . get_string('externalname', 'report_ee', $externalexaminer) . "</p>";
  $submittedby = $USER->firstname . " " . $USER->lastname;
  $messagebody .= "<p>" . get_string('submittedby', 'report_ee', $submittedby) . "</p>";
  $messagebody .= $assignmessage;
  $messagebody .= "<h4>" ."Comments:</h4><p>" . $formdata->comments . "</p>";
  $url = new moodle_url('/report/ee/index.php', array('id'=>$COURSE->id));
  $messagebody .= "<p><a href='". $url . "'>" . get_string('reportlink', 'report_ee'). "</a></p>";
  mail($to, $subject, $messagebody, $headers);
}
