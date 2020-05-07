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
// along with Moodle.  If not, sexternalexaminer <http://www.gnu.org/licenses/>.

/**
 * Form for external examines to review assignments
 *
 * @package    report_ee
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

function get_assignments($course){
  global $DB, $USER, $COURSE;

  $assignments = $DB->get_records_sql(' SELECT a.id, a.name, cm.idnumber
          FROM {assign} a
          JOIN {course_modules} cm ON cm.instance = a.id
          JOIN {modules} m ON m.id = cm.module AND m.name = "assign"
          WHERE a.course = ?', array($course));

  return $assignments;
}

function get_coursefullname($course){
  global $DB, $USER;
    $coursefullname = $DB->get_field_select("course", "fullname", "id=$course");


  return $coursefullname;
}

function get_rowid($assignmentid){
  global $DB, $USER;
    $rowid = $DB->get_field_select("report_ee", "id", "assignid=$assignmentid");


  return $rowid;
}

function getformvalues($assignids) {
  global $DB, $USER;

  $records = $DB->get_records_sql ( "SELECT * FROM {report_ee} WHERE assignid IN (" . rtrim ( $assignids, ',' ) . ")" );

  return $records;
}

function save_form_data($formdata){
  var_dump($formdata);die();
  // if (isset($formdata['formlock'])) {
  //     $locked = 1;
  // } else {
  //     $locked = 0;
  // }

  // $results = array();
  // $ids = array();
  // $recordstosave = array();
  //
  // // TODO get the date/time it was locked
  // // $locked = 0;
  //
  // foreach ($formdata as $key => $val) {
  //   // get the unique ids if they are numeric
  //     $ids[] .= strtok($key, '_');
  //     $ids = array_unique($ids);
  // }
  //
  // foreach ($ids as $id => $idval) {
  //     if (!is_numeric($idval)) {
  //         unset($ids[$id]);
  //     }
  // }
  //
  // $recordstosave = array();
  //
  // foreach ($ids as $key => $value) {
  //     foreach($formdata as $form => $data) {
  //         $criterion = substr($form, -1);
  //         $recordstosave[strtok($form, '_')][$criterion] = $data;
  //     }
  // }
  //
  // foreach($recordstosave as $k=>$v) {
  //     $date = new DateTime("now", core_date::get_user_timezone_object());
  //     $date->setTime(0, 0, 0);
  //
  //     $saveclass =new stdClass();
  //     if(is_numeric($k)) {
  //       $saveclass->userid = $USER->id;
  //       $saveclass->assignid = $k;
  //       $saveclass->sample = $v['sample'];
  //       //$saveclass->criterionb = $v['b'];
  //     //  $saveclass->criterionc = $v['c'];
  //       $saveclass->comments = $recordstosave['comments']['s'];
  //       $saveclass->locked = $locked;
  //       $saveclass->timemodified = $date->getTimestamp();
  //
  //       $update = $DB->record_exists('report_ee', ['assignid' => $saveclass->assignid]);
  //
  //       // if the record does not exist, insert it
  //       if(!$update) {
  //           $saveclassid = $DB->insert_record('report_ee', $saveclass, true);
  //       } else {
  //           $saveclass->id = get_rowid($saveclass->assignid);
  //           $saveclassid = $DB->update_record('report_ee', $saveclass, true);
  //       }
  //   }
  // }
}
