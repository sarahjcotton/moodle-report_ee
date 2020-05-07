<?php

/**
 * @package   report_ee
 * @copyright 2020, Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function report_ee_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/ee:view', $context)) {
        $url = new moodle_url('/report/ee/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_ee'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
