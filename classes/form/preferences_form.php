<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Notification preferences form for local_fastcomments.
 *
 * @package    local_fastcomments
 * @copyright  2026 FastComments
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_fastcomments\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for editing FastComments notification preferences.
 */
class preferences_form extends \moodleform {

    /**
     * Define the form elements.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'advcheckbox',
            'optedinnotifications',
            get_string('pref_optedinnotifications', 'local_fastcomments')
        );
        $mform->addHelpButton('optedinnotifications', 'pref_optedinnotifications', 'local_fastcomments');
        $mform->setDefault('optedinnotifications', 1);

        $mform->addElement(
            'advcheckbox',
            'optedinsubscriptionnotifications',
            get_string('pref_optedinsubscriptionnotifications', 'local_fastcomments')
        );
        $mform->addHelpButton('optedinsubscriptionnotifications', 'pref_optedinsubscriptionnotifications', 'local_fastcomments');
        $mform->setDefault('optedinsubscriptionnotifications', 1);

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
