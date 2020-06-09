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

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/report/ee/index.php', array('id'=>$course));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'report_ee'));

require_login($course);
$this->page->requires->js_call_amd('report_ee/submit', 'init', array());

// Check permissions.
$coursecontext = context_course::instance($course);
require_capability('report/ee:view', $coursecontext);

$PAGE->set_heading(get_string('pluginname', 'report_ee'));

echo $OUTPUT->header();

$data = get_report_data($course);
$setdata = process_data($data);

if((has_capability('report/ee:view', $coursecontext) && $setdata->locked != 0) && !is_siteadmin()){
  $locked = $setdata->locked;
}else{
  $locked = 0;
}

$mform = new externalexaminerform(null, array('course' => $course, 'locked'=>$locked));
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    /// redirect to course page
    redirect($CFG->wwwroot.'/course/view.php?id=' . $course, get_string('cancel', 'report_ee'), null, \core\output\notification::NOTIFY_SUCCESS);
} else if ($formdata = $mform->get_data()) {
  $saved = save_form_data($formdata);
  redirect($CFG->wwwroot.'/course/view.php?id=' . $course, get_string('saved', 'report_ee'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$mform->set_data($setdata);
$mform->display();

echo $OUTPUT->footer();
