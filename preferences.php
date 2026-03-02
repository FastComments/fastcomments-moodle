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
 * User notification preferences page for local_fastcomments.
 *
 * @package    local_fastcomments
 * @copyright  2026 FastComments
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/fastcomments/preferences.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('preferences_title', 'local_fastcomments'));
$PAGE->set_heading(get_string('preferences_title', 'local_fastcomments'));

$profileurl = new moodle_url('/user/profile.php');

$form = new \local_fastcomments\form\preferences_form();

if ($form->is_cancelled()) {
    redirect($profileurl);
}

if ($data = $form->get_data()) {
    set_user_preference('local_fastcomments_optedinnotifications', $data->optedinnotifications);
    set_user_preference('local_fastcomments_optedinsubscriptionnotifications', $data->optedinsubscriptionnotifications);
    redirect($profileurl, get_string('preferences_saved', 'local_fastcomments'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

// Load current preferences as defaults.
$defaults = new stdClass();
$defaults->optedinnotifications = get_user_preferences('local_fastcomments_optedinnotifications', 1);
$defaults->optedinsubscriptionnotifications = get_user_preferences('local_fastcomments_optedinsubscriptionnotifications', 1);
$form->set_data($defaults);

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
