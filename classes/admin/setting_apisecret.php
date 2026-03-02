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
 * Custom admin setting for the API secret that validates it is set when Secure SSO is selected.
 *
 * @package    local_fastcomments
 * @copyright  2026 FastComments
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_fastcomments\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * API secret setting that requires a value when SSO type is "secure".
 */
class setting_apisecret extends \admin_setting_configpasswordunmask {

    /**
     * Validate the API secret value.
     *
     * @param string $data The submitted value.
     * @return true|string True if valid, error string otherwise.
     */
    public function validate($data) {
        $ssotype = get_config('local_fastcomments', 'ssotype');
        if (($ssotype === 'secure' || $ssotype === false) && empty($data)) {
            return get_string('setting_apisecret_required', 'local_fastcomments');
        }
        return true;
    }
}
