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

require('../../config.php');
require_once('form.php');
require_once('locallib.php');

$id = optional_param('id', '', PARAM_INT);
$courseid = optional_param('course', '', PARAM_INT);
$course = ($id ? $id : $courseid);

//$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/report/ee/index.php', array('id'=>$course));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'report_ee'));

require_login($course);

// Check permissions.
$coursecontext = context_course::instance($course);
require_capability('report/ee:view', $coursecontext);

$PAGE->set_heading(get_string('pluginname', 'report_ee'));

echo $OUTPUT->header();

$mform = new externalexaminerform(null, array('course' => $course));
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    /// redirect to course page
    redirect($CFG->wwwroot.'/course/view.php?id=' . $course, get_string('cancel', 'report_ee'), null, \core\output\notification::NOTIFY_SUCCESS);
} else if ($formdata = $mform->get_data()) {
  $saved = save_form_data($formdata);

// } else {
//   // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
//   // or on the first display of the form.
//
//     // set the data from the database
//
//     // check if there is data
//     $isdata = $DB->record_exists('report_ee', ['assignid' => $course]);
//
//     if(!$isdata) {
//         $toform = '';
//     } else {
//         $assigns = get_assignments($course);
//         $assigns = json_decode(json_encode($assigns), true);
//         // print_r($assigns);
//
//
//         $assignids = '';
//         foreach($assigns as $assign) {
//             $assignids .= $assign['id'] . ',';
//         }
//         // print_r($assignids);
//
//         $formvalues = getformvalues($assignids);
// $toform = new stdClass;
//
//         $formvals = json_decode(json_encode($formvalues), true);
//         foreach ($formvals as $formval) {
//
//             $formassign = $formval['assignid'];
// if (isset($formval['criteriona'])) {
//             $formassign .= '_a';
//
//                     // foreach ($formval['assignid'] as $formassign) {
//                     // print('xxx');
//                     // print($formassign);
//                     $toform->$formassign = $formval['criteriona'];
//                 }
//
//                 if (isset($formval['criterionb'])) {
//                     $formassign = $formval['assignid'];
//             $formassign .= '_b';
//
//                     // foreach ($formval['assignid'] as $formassign) {
//                     // print('xxx');
//                     // print($formassign);
//                     $toform->$formassign = $formval['criterionb'];
//                 }
//
//                                 if (isset($formval['criterionc'])) {
//                     $formassign = $formval['assignid'];
//             $formassign .= '_c';
//
//                     // foreach ($formval['assignid'] as $formassign) {
//                     // print('xxx');
//                     // print($formassign);
//                     $toform->$formassign = $formval['criterionc'];
//                 }
//
//
//                     // }
//             //             if(isset($formval['criteriona'])) {
//             //     $toform->$formval['assignid'] = $formval['criteriona'];
//             // }
//
//
//
//
//             // print_r($formval['id']);
//                         if(isset($formval['userid'])) {
//                 $toform->userid = $formval['userid'];
//             }
//             if(isset($formval['locked'])) {
//                 $toform->formlock = $formval['locked'];
//             }
//             $toform->comments = $formval['comments'];
//
//          // print_r($formval);
//         }
//
//
//         // print_r($toform);
//
//         // $toform = '';
//
//     }
//
//     $mform->set_data($toform);
//
//   //displays the form
}
$mform->display();
echo $OUTPUT->footer();
