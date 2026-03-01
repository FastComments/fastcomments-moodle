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
 * Privacy API implementation for local_fastcomments.
 *
 * @package    local_fastcomments
 * @copyright  2026 FastComments
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_fastcomments\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;

/**
 * Privacy provider for local_fastcomments.
 *
 * Declares external data sent to the FastComments service.
 * No local database tables are used, so no data export/deletion
 * is needed within Moodle — FastComments handles that directly.
 */
class provider implements metadata_provider {

    /**
     * Describe the external data sent to FastComments.
     *
     * @param collection $collection The privacy metadata collection.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'fastcomments',
            [
                'userid'   => 'privacy:metadata:fastcomments:userid',
                'email'    => 'privacy:metadata:fastcomments:email',
                'fullname' => 'privacy:metadata:fastcomments:fullname',
                'avatar'   => 'privacy:metadata:fastcomments:avatar',
            ],
            'privacy:metadata:fastcomments'
        );

        return $collection;
    }
}
