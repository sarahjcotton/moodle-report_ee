<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
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
 * Locallib file for external examiners
 *
 * @package   report_ee
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get all first sitting assignments.
 *
 * @param int $course Courseid
 * @return array Assignments for specified courseid
 */
function report_ee_get_assignments($course) {
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

/**
 * Course fullname
 *
 * @param int $course
 * @return string
 */
function report_ee_get_course_fullname($course) {
    global $DB;
    // Not safe SQL.
    $coursefullname = $DB->get_field_select("course", "fullname", "id=$course");
    return $coursefullname;
}

/**
 * Save form data
 *
 * @param stdClass $formdata
 * @return void
 */
function report_ee_save_form_data($formdata) {
    global $DB, $USER;
    $course = $formdata->course;
    // Check to see if record exists in ee table for course.
    $reportrecord = $DB->get_record('report_ee', ['course' => $course]);
    if (!$reportrecord) {
        $record = new stdClass();
        $record->course = $course;
        $record->comments = $formdata->comments;
        if ($formdata->locked == 1) {
            $date = new DateTime("now", core_date::get_user_timezone_object());
            $record->locked = $date->getTimestamp();
        } else {
            $record->locked = $formdata->locked;
        }
        $date = new DateTime("now", core_date::get_user_timezone_object());
        $record->timecreated = $date->getTimestamp();
        $reportrecord = $DB->insert_record('report_ee', $record, true);
    } else {
        $reportrecord = $reportrecord->id;
    }

    // If form record exists, get id and add assignments to ee_assign table (add record to assign table for easy joining).
    $record = new stdClass();
    $record->report = $reportrecord;
    $record->user = $USER->id;

    $record2 = new stdClass();
    $record2->id = $reportrecord;

    foreach ($formdata as $data => $d) {
        $arr = explode("_", $data);
        if ($arr[0] == 'assign') {
            $record->assign = $arr[1];
            // Get the assign id and  value.
            if ($arr[1] == $record->assign) {
                if ($arr[2] == 'sample') {
                    $record->sample = $d;
                } else if ($arr[2] == 'level') {
                    $record->level = $d;
                } else if ($arr[2] == 'national') { // When we get here we need to process the record.
                    $record->national = $d;
                    $assignrecord = $DB->get_record('report_ee_assign',
                        ['report' => $reportrecord->id, 'assign' => $record->assign]);
                    if (!$assignrecord) {
                        $DB->insert_record('report_ee_assign', $record, true);
                    } else {
                        $record->id = $assignrecord->id;
                        $DB->update_record('report_ee_assign', $record, false);
                    }
                }
            }
            $assign = $arr[1];
        } else {
            if ($data == 'comments') {
                $record2->comments = $d;
            } else if ($data == 'locked') {
                if ($d == 1) {
                    $date = new DateTime("now", core_date::get_user_timezone_object());
                    $record2->locked = $date->getTimestamp();
                } else {
                    $record2->locked = $d;
                }
            }
        }
    }
    $date = new DateTime("now", core_date::get_user_timezone_object());
    $record2->timemodified = $date->getTimestamp();
    $DB->update_record('report_ee', $record2, false);
}

/**
 * Given courseid, get assignment reports for that course
 *
 * @param int $course courseid
 * @return void
 */
function report_ee_get_report_data($course) {
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

/**
 * Get existing data to populate the form
 *
 * @param array $data
 * @return stdClass Form data
 */
function report_ee_process_data($data) {
    $assign = 0;
    $username = null;
    $setdata = new stdClass();

    foreach ($data as $key => $val) {
        foreach ($val as $k => $v) {
            // This does assume assign will always be first. Perhaps use array filters or not even loop.
            if ($k === 'assign') {
                $assign = $v;
            }
            if ($k == 'sample') {
                $sample = 'assign_'. $assign .'_sample';
                $setdata->{$sample} = $v;
            }
            if ($k == 'level') {
                $level = 'assign_'. $assign .'_level';
                $setdata->{$level} = $v;
            }
            if ($k == 'national') {
                $national = 'assign_'. $assign .'_national';
                $setdata->{$national} = $v;
            }
            if ($k == 'comments') {
                $setdata->comments = $v;
            }
            if ($k == 'username') {
                $username = $v;
            }
            if ($k == 'locked') {
                if ($v == 0) {
                    $setdata->locked = $v;
                } else {
                    $setdata->locked = 1;
                    $date = new DateTime();
                    $date->setTimestamp(intval($v));
                    $date = userdate($date->getTimestamp());
                    $setdata->lockedby = get_string('lockedbydata', 'report_ee', ['username' => $username, 'date' => $date]);
                }
            }
        }
    }
    return $setdata;
}

/**
 * Get email addresses for the module leaders as a CSV
 *
 * @return stdClass
 */
function report_ee_get_module_leader_emails() {
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

/**
 * Get the external examiner on current course
 *
 * @return stdClass
 */
function report_ee_get_external_examiner() {
    global $DB, $COURSE;
    // Could there be more than one?
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

/**
 * Get field label
 *
 * @param string $string
 * @return string label
 */
function report_ee_get_label_string($string) {
    switch ($string) {
        case 'sample':
            $string = get_string('sample', 'report_ee');
            return $string;
        case 'level':
            $string = get_string('level', 'report_ee');
            return $string;
        case 'national':
            $string = get_string('national', 'report_ee');
            return $string;
        default:
            return "";
    }
}

/**
 * Send emails when EE report has been completed.
 *
 * @param stdClass $formdata EE form data
 * @return void
 */
function report_ee_send_emails($formdata) {
    global $DB, $COURSE, $USER, $CFG;
    $assign = 0;
    $assignmessage = "";
    $actionrequired = "";
    $to = report_ee_get_module_leader_emails()->emailto . ',' . get_config('report_ee', 'studentregemail');
    $negativeoutcometext = "";

    foreach ($formdata as $data => $d) {
        $arr = explode("_", $data);
        if ($arr[0] == 'assign') { // If this is an assignment value.
            if ($arr[1] !== $assign) {
                // Get assign name.
                $assignment = $DB->get_record('assign', array('id' => $arr[1]));
                $assignmessage .= "<h4>Assignment - " . $assignment->name . "</h4>";
            }

            switch ($d) {
                case 1:
                    $assignmessage .= "<p>" . report_ee_get_label_string($arr[2]) . " - Yes</p>";
                    $subject = '';
                    break;
                case 2:
                    $assignmessage .= '<p style="color:red;font-weight:bold;">' .
                        report_ee_get_label_string($arr[2]) . " - No </p>";
                    $actionrequired = get_string('actionrequired', 'report_ee');
                    // This is something QA need to know about.
                    $to .= ',' . get_config('report_ee', 'qualityemail');
                    $negativeoutcometext = '<p style="font-weight:bold;">' .
                        get_string('negativeoutcometext', 'report_ee') . "</p>";
                    break;
            }
            $assign = $arr[1];
        }
    }

    $startdate = new DateTime();
    $startdate->setTimestamp($COURSE->startdate);
    $startdate = userdate($startdate->getTimestamp(), '%d/%m/%Y');

    $enddate = new DateTime();
    $enddate->setTimestamp($COURSE->enddate);
    $enddate = userdate($enddate->getTimestamp(), '%d/%m/%Y');

    $shortname = substr($COURSE->shortname, 0, strpos($COURSE->shortname, "_"));

    $subject = $actionrequired . get_string('subject', 'report_ee', $shortname) . " " . $startdate . " - " . $enddate;
    $headers = "From: " . $CFG->noreplyaddress . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $externalexaminer = report_ee_get_external_examiner()->name;
    $messagebody = "<p>" . get_string('externalname', 'report_ee', $externalexaminer) . "</p>";
    $submittedby = $USER->firstname . " " . $USER->lastname;
    $messagebody .= "<p>" . get_string('submittedby', 'report_ee', $submittedby) . "</p>";
    $messagebody .= $assignmessage;
    $messagebody .= "<h4>" ."Comments:</h4><p>" . $formdata->comments . "</p>";
    $messagebody .= "<p>" . $negativeoutcometext . "</p>";
    $url = new moodle_url('/report/ee/index.php', array('id' => $COURSE->id));
    $messagebody .= "<p><a href='" . $url . "'>" . get_string('reportlink', 'report_ee'). "</a></p>";
    mail($to, $subject, $messagebody, $headers);
}
