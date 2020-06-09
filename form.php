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

        // get the assignments
        $assignments = get_assignments($this->_customdata['course']);
        $coursefullname = get_coursefullname($this->_customdata['course']);
        $locked = $this->_customdata['locked'];

        foreach($assignments as $assign) {
       // IMPORTANT: add validation and type rules as per documentation
            // Add the assignment name
            $mform->addElement('html', '<h4>' . $assign->name. '</h4>');
            // Samples select
            $sampleid = 'assign_' . $assign->id.'_sample';
            $mform->addElement('select', $sampleid, get_string('sample', 'report_ee') , array('Select', 'Yes', 'No'), $attributes='class="dropdown sample" name="sample"');
            $mform->addHelpButton($sampleid, 'helpsample', 'report_ee');
            if($locked != 0){
              $mform->disabledIf($sampleid, 'locked', 'checked');
            }

            $levelid = 'assign_' . $assign->id.'_level';
            $mform->addElement('select', $levelid, get_string('level', 'report_ee'), array('Select', 'Yes', 'No'), $attributes='class="dropdown" name="level"');
            $mform->addHelpButton($levelid, 'helpsample', 'report_ee');
            if($locked != 0){
              $mform->disabledIf($levelid, 'locked', 'checked');
            }

            $nationalid = 'assign_' . $assign->id.'_national';
            $mform->addElement('select', $nationalid, get_string('national', 'report_ee'), array('Select', 'Yes', 'No'), $attributes='class="dropdowns"');
            $mform->addHelpButton($nationalid, 'helpsample', 'report_ee');
            if($locked != 0){
                $mform->disabledIf($nationalid, 'locked', 'checked');
            }
        }

        // Add comments section
        $mform->addElement('textarea', 'comments', get_string('comments', 'report_ee'), 'wrap="virtual" rows="20" cols="50"');
        $mform->addElement('advcheckbox', 'locked', get_string('lock', 'report_ee'), null);
        if($locked != 0){
          $mform->disabledIf('comments', 'locked', 'checked');
        }
        // TODO check if all the assignments have been evaluated
        $mform->addHelpButton('locked', 'helplock', 'report_ee');
        if($locked != 0){
          $mform->hardFreeze('locked');
        }

        $mform->addElement('hidden', 'course', $this->_customdata['course']);
        $mform->setType('course', PARAM_INT);
        // $mform->addElement('hidden', 'preventedit', $locked);
        // $mform->setType('preventedit', PARAM_INT);

        if($locked != 0){
          $mform->addElement('static', 'lockedby', get_string('lockedby', 'report_ee'));
          //$this->add_action_buttons($cancel = true, $submitlabel=null);
        }
        $this->add_action_buttons();
        // else{
        //   $this->add_action_buttons();
        // }
    }

    public function validation($data, $files) {
  			$errors = parent::validation($data, $files);

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

class confirmform extends moodleform{
    /**
     * Defines forms elements
     */
    public function definition() {
      $mform = $this->_form;
      $mform->addElement('html', '<h4> Confirm form</h4>');
      $mform->addElement('html', '<p>Are you sure you want to submit this form? No further changes will be able to be made.</p>');
      //$mform->addElement('advcheckbox', 'locked', get_string('lock', 'report_ee'), null);

      $mform->addElement('hidden', 'course', $this->_customdata['formdata']->course);
      $mform->setType('course', PARAM_INT);

      print_object($this->_customdata);

      $this->add_action_buttons();
    }
}
