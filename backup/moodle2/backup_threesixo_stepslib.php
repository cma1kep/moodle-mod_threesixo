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
 * Define all the backup steps that will be used by the backup_threesixo_activity_task
 */

/**
 * Define the complete threesixo structure for backup, with file and id annotations
 */
class backup_threesixo_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $threesixo = new backup_nested_element('threesixo', array('id'), array(
            'name', 'intro', 'introformat', 'anonymous',
            'participantrole', 'email_notification', 'status',
            'with_self_review', 'timeopen', 'timeclose', 'timemodified',
            'releasing', 'release', 'undodecline'));

        $options = new backup_nested_element('items');

        $option = new backup_nested_element('option', array('id'), array(
            'text', 'maxanswers', 'timemodified'));

        $answers = new backup_nested_element('answers');

        $answer = new backup_nested_element('answer', array('id'), array(
            'userid', 'optionid', 'timemodified'));

        // Build the tree
        $threesixo->add_child($options);
        $options->add_child($option);

        $threesixo->add_child($answers);
        $answers->add_child($answer);

        // Define sources
        $threesixo->set_source_table('threesixo', array('id' => backup::VAR_ACTIVITYID));

        $option->set_source_table('threesixo_options', array('threesixoid' => backup::VAR_PARENTID), 'id ASC');

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $answer->set_source_table('threesixo_answers', array('threesixoid' => '../../id'));
        }

        // Define id annotations
        $answer->annotate_ids('user', 'userid');

        // Define file annotations
        $threesixo->annotate_files('mod_threesixo', 'intro', null); // This file area hasn't itemid

        // Return the root element (threesixo), wrapped into standard activity structure
        return $this->prepare_activity_structure($threesixo);
    }
}
