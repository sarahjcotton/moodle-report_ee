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
 * Form for external examiners to review assignments
 *
 * @package    report_ee
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('form.php');
require_once('locallib.php');

$id = optional_param('id', '', PARAM_INT);
$courseid = optional_param('course', '', PARAM_INT);
$course = ($id ? $id : $courseid);

$PAGE->set_url('/report/ee/index.php', array('id'=>$course));
$PAGE->set_pagelayout('report');

require_login($course);

// Check permissions.
$coursecontext = context_course::instance($course);
$PAGE->set_context($coursecontext);
require_capability('report/ee:view', $coursecontext);
$PAGE->set_title($COURSE->shortname .': '. get_string('pluginname' , 'report_ee'));
$PAGE->set_heading(get_string('pluginname', 'report_ee'));

echo $OUTPUT->header();

// Trigger a report viewed event.
$event = \report_ee\event\report_viewed::create(array(
            'context' =>  $coursecontext,
            'userid' => $USER->id,
          ));
$event->trigger();

$data = report_ee_get_report_data($course);
$setdata = report_ee_process_data($data);

if($data){
  if($setdata->locked != 0){
    $locked = $setdata->locked;
  }else{
    $locked = 0;
  }
}else{
  $locked = 0;
}

if(has_capability('report/ee:admin', $coursecontext)){
  $admin = true;
}else{
  $admin = false;
}
if(has_capability('report/ee:edit', $coursecontext)){
  $edit = true;
}else{
  $edit = false;
}
$PAGE->requires->js_call_amd('report_ee/submit', 'init', [$admin]);

$mform = new externalexaminerform(null, array('course' => $course, 'locked'=>$locked, 'admin'=>$admin, 'edit'=>$edit));
if ($mform->is_cancelled()) {
  redirect($CFG->wwwroot.'/course/view.php?id=' . $course, get_string('cancel', 'report_ee'), null, \core\output\notification::NOTIFY_SUCCESS);
} else if ($formdata = $mform->get_data()) {
  report_ee_save_form_data($formdata);
  if($formdata->locked !=0){
    report_ee_send_emails($formdata, $data);
    // Trigger a report submitted event.
    $event = \report_ee\event\report_submitted::create(array(
                'context' =>  $coursecontext,
                'userid' => $USER->id,
              ));
    $event->trigger();
  }elseif($formdata->locked < $setdata->locked){
    // Trigger a report submitted event.
    $event = \report_ee\event\report_unlocked::create(array(
                'context' =>  $coursecontext,
                'userid' => $USER->id,
              ));
    $event->trigger();
  }else{
    // Trigger a report submitted event.
    $event = \report_ee\event\report_updated::create(array(
                'context' =>  $coursecontext,
                'userid' => $USER->id,
              ));
    $event->trigger();
  }
  redirect($CFG->wwwroot.'/course/view.php?id=' . $course, get_string('saved', 'report_ee'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$mform->set_data($setdata);
$mform->display();

echo $OUTPUT->footer();
