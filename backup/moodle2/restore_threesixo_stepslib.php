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
 * @package    mod_threesixo
 * @subpackage backup-moodle2
 * @copyright 2019 onwards Jun Pataleta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_threesixo_activity_task
 */

/**
 * Structure step to restore one threesixo activity
 */
class restore_threesixo_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('threesixo', '/activity/threesixo');
        $paths[] = new restore_path_element('threesixo_option', '/activity/threesixo/options/option');
        if ($userinfo) {
            $paths[] = new restore_path_element('threesixo_answer', '/activity/threesixo/answers/answer');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_threesixo($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        // insert the threesixo record
        $newitemid = $DB->insert_record('threesixo', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_threesixo_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->threesixoid = $this->get_new_parentid('threesixo');

        $newitemid = $DB->insert_record('threesixo_options', $data);
        $this->set_mapping('threesixo_option', $oldid, $newitemid);
    }

    protected function process_threesixo_answer($data) {
        global $DB;

        $data = (object)$data;

        $data->threesixoid = $this->get_new_parentid('threesixo');
        $data->optionid = $this->get_mappingid('threesixo_option', $data->optionid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('threesixo_answers', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add threesixo related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_threesixo', 'intro', null);
    }
}
