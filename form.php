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
 * EE form
 */
class externalexaminerform extends moodleform{
    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER, $DB, $CFG, $OUTPUT;
        $mform = $this->_form;

        // Get the assignments
        $assignments = get_assignments($this->_customdata['course']);
        $coursefullname = get_course_fullname($this->_customdata['course']);
        $locked = $this->_customdata['locked'];
        $edit = $this->_customdata['edit'];
        $admin = $this->_customdata['admin'];

        foreach($assignments as $assign) {
          // Add the assignment elements
          $mform->addElement('html', '<h4>' . $assign->name. '</h4>');
          // Samples select
          $sampleid = 'assign_' . $assign->id.'_sample';
          $mform->addElement('select', $sampleid, get_string('sample', 'report_ee') , array('Select', 'Yes', 'No'), $attributes='class="dropdown sample" name="sample"');
          $mform->addHelpButton($sampleid, 'helpsample', 'report_ee');
          if($locked != 0 || $edit == false){
            $mform->hardFreeze($sampleid);
          }
          // Level select
          $levelid = 'assign_' . $assign->id.'_level';
          $mform->addElement('select', $levelid, get_string('level', 'report_ee'), array('Select', 'Yes', 'No'), $attributes='class="dropdown" name="level"');
          $mform->addHelpButton($levelid, 'helplevel', 'report_ee');
          if($locked != 0 || $edit == false){
            $mform->hardFreeze($levelid);
          }
          // National select
          $nationalid = 'assign_' . $assign->id.'_national';
          $mform->addElement('select', $nationalid, get_string('national', 'report_ee'), array('Select', 'Yes', 'No'), $attributes='class="dropdowns"');
          $mform->addHelpButton($nationalid, 'helpnational', 'report_ee');
          if($locked != 0 || $edit == false){
              $mform->hardFreeze($nationalid);
          }
        }

        // Comments text area
        $mform->addElement('textarea', 'comments', get_string('comments', 'report_ee'), 'wrap="virtual" rows="20" cols="50"');
        if($locked != 0 || $edit == false){
          $mform->disabledIf('comments', 'locked', 'checked');
          $mform->hardFreeze('comments');
        }
        // Locked checkbox
        $mform->addElement('advcheckbox', 'locked', get_string('lock', 'report_ee'), null);
        $mform->addHelpButton('locked', 'helplock', 'report_ee');
        // Only allow EEs to lock the form. Only allow Student registry to unlock the form
        if(($locked != 0 && $admin == false)){
          $mform->hardFreeze('locked');
        }
        if($edit == false && $admin == false || $locked == 0 && $admin == true){
          $mform->hardFreeze('locked');
        }
        // Locked warning populated via jQuery onclick of 'locked'
        $mform->addElement('html', '<div class="lockedwarning">');
        $mform->addElement('html', '</div><br>');

        $mform->addElement('hidden', 'course', $this->_customdata['course']);
        $mform->setType('course', PARAM_INT);
        // Locked by info populated via setdata()
        if($locked != 0){
          $mform->addElement('static', 'lockedby', get_string('lockedby', 'report_ee'));
        }
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
  			$errors = parent::validation($data, $files);
        // On final submit (locked) check all fields have been populated
        if($data['locked'] == 1){
          foreach($data as $k=>$v){
            if((strpos($k, 'assign') === 0) && ($v == 0)){
              $errors[$k] = get_string('errselect', 'report_ee');
            }
            if($k == 'comments' && $v == ""){
              $errors[$k] = get_string('errcomment', 'report_ee');
            }
    			}
        }

        return $errors;
    }
}
