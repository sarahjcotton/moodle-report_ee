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
 * Behat plugin generator
 *
 * @package    report_ee
 * @category   test
 * @copyright  2023 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_report_ee_generator extends behat_generator_base {
    /**
     * Adds 'and the following "report_ee > eefeedback" exists:' step
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'eefeedbacks' => [
                'singular' => 'eefeedback',
                'datagenerator' => 'eefeedback',
                'required' => ['course', 'activity'],
                'switchids' => [
                    'course' => 'courseid',
                    'activity' => 'instanceid',
                ],
            ],
        ];
    }

    /**
     * Given an activity idnumber, returns the instanceid
     *
     * @param string $activityname
     * @return  int instance id
     */
    protected function get_activity_id(string $activityname): int {
        global $DB;
        $sql = "SELECT cm.instance
            FROM {course_modules} cm
            WHERE cm.idnumber = :idnumber";
        $id = $DB->get_field_sql($sql, ['idnumber' => $activityname]);
        if (empty($id)) {
            throw new Exception("There is no Course Module with this idnumber {$activityname}");
        }
        return $id;
    }
}
