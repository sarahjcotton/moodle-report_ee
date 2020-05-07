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

require_once("$CFG->libdir/formslib.php");
require_once('locallib.php');
/**
 * Example form
 */
class externalexaminerform extends moodleform{
    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER, $DB, $CFG, $OUTPUT;
        $mform = $this->_form;

        // get the assignments
        $assignments = get_assignments($this->_customdata['course']);
        $coursefullname = get_coursefullname($this->_customdata['course']);

        foreach($assignments as $assign) {
       // IMPORTANT: add validation and type rules as per documentation
            // Add the assignment name
            $mform->addElement('html', '<h4>' . $assign->name. '</h4>');
            // Samples select
            $sampleid = $assign->id.'_sample';
            $mform->addElement('select', $sampleid, get_string('sample', 'report_ee') , array('Select', 'Yes', 'No'), $attributes='class="dropdown sample" name="sample"');
            $mform->addHelpButton($sampleid, 'helpsample', 'report_ee');
            $mform->disabledIf($sampleid, 'formlock', 'checked');

            // $mform->addElement('select', $assign->id .'_level', get_string('levels', 'report_ee'), array('', 'No', 'Yes'), $attributes='class="dropdowns"');
            // $selectid_b = $assign->id.'_level';
            // $mform->addHelpButton($selectid_b, 'helpsamples', 'report_ee');
            // $mform->disabledIf($assign->id .'_level', 'formlock', 'checked');
            //
            // $mform->addElement('select', $assign->id .'_c', get_string('national', 'report_ee'), array('', 'No', 'Yes'), $attributes='class="dropdowns"');
            // $selectid_c = $assign->id.'_c';
            // $mform->addHelpButton($selectid_c, 'helpsamples', 'report_ee');
            // $mform->disabledIf($assign->id .'_c', 'formlock', 'checked');
        }

        // Add comments section
        $mform->addElement('textarea', 'comments', get_string('comments', 'report_ee'), 'wrap="virtual" rows="20" cols="50"');
        $mform->addElement('advcheckbox', 'formlock', get_string('lock', 'report_ee'), null);
        // $mform->setDefault('formlock', 1);
        $mform->disabledIf('comments', 'formlock', 'checked');
        // TODO check if all the assignments have bexternalexaminern evaluated
        $mform->addHelpButton('formlock', 'helplock', 'report_ee');
        // TODO check whether this should be displayed
        $mform->addElement('checkbox', 'unlock', get_string('unlock', 'report_ee'));
        $mform->addElement('hidden', 'course', $this->_customdata['course']);
        $mform->setType('course', PARAM_INT);
        $this->add_action_buttons();
    }
}
