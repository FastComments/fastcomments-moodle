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
 * Admin settings for local_fastcomments.
 *
 * @package    local_fastcomments
 * @copyright  2026 FastComments
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_fastcomments', get_string('pluginname', 'local_fastcomments'));

    // Tenant ID.
    $settings->add(new admin_setting_configtext(
        'local_fastcomments/tenantid',
        get_string('setting_tenantid', 'local_fastcomments'),
        get_string('setting_tenantid_desc', 'local_fastcomments'),
        ''
    ));

    // API Secret (password field so it's masked, required when Secure SSO is selected).
    $settings->add(new \local_fastcomments\admin\setting_apisecret(
        'local_fastcomments/apisecret',
        get_string('setting_apisecret', 'local_fastcomments'),
        get_string('setting_apisecret_desc', 'local_fastcomments'),
        ''
    ));

    // SSO Type.
    $ssooptions = [
        'secure' => get_string('setting_ssotype_secure', 'local_fastcomments'),
        'simple' => get_string('setting_ssotype_simple', 'local_fastcomments'),
        'none'   => get_string('setting_ssotype_none', 'local_fastcomments'),
    ];
    $settings->add(new admin_setting_configselect(
        'local_fastcomments/ssotype',
        get_string('setting_ssotype', 'local_fastcomments'),
        get_string('setting_ssotype_desc', 'local_fastcomments'),
        'secure',
        $ssooptions
    ));

    // Page contexts (which page types get comments).
    $settings->add(new admin_setting_configmulticheckbox(
        'local_fastcomments/pagecontexts',
        get_string('setting_pagecontexts', 'local_fastcomments'),
        get_string('setting_pagecontexts_desc', 'local_fastcomments'),
        ['module' => 1],
        [
            'course' => get_string('setting_context_course', 'local_fastcomments'),
            'module' => get_string('setting_context_module', 'local_fastcomments'),
        ]
    ));

    // Commenting Style.
    $styleoptions = [
        'comments' => get_string('setting_commentstyle_comments', 'local_fastcomments'),
        'collabchat_comments' => get_string('setting_commentstyle_collabchat_comments', 'local_fastcomments'),
        'collabchat' => get_string('setting_commentstyle_collabchat', 'local_fastcomments'),
    ];
    $settings->add(new admin_setting_configselect(
        'local_fastcomments/commentstyle',
        get_string('setting_commentstyle', 'local_fastcomments'),
        get_string('setting_commentstyle_desc', 'local_fastcomments'),
        'comments',
        $styleoptions
    ));

    // CDN URL.
    $settings->add(new admin_setting_configtext(
        'local_fastcomments/cdn',
        get_string('setting_cdn', 'local_fastcomments'),
        get_string('setting_cdn_desc', 'local_fastcomments'),
        'https://cdn.fastcomments.com',
        PARAM_URL
    ));

    $ADMIN->add('localplugins', $settings);
}
