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
 * Core library for local_fastcomments.
 *
 * Provides the before_footer() callback that injects the FastComments
 * widget into Moodle pages, with support for Secure SSO, Simple SSO,
 * and anonymous commenting.
 *
 * @package    local_fastcomments
 * @copyright  2026 FastComments
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Injects the FastComments widget before the page footer.
 *
 * Called automatically by Moodle on every page render.
 * Checks configuration, page context, and builds the appropriate
 * SSO config before outputting the widget HTML + JS.
 *
 * @return string HTML to inject, or empty string if not applicable.
 */
function local_fastcomments_before_footer() {
    global $PAGE, $USER, $CFG;

    $tenantid = get_config('local_fastcomments', 'tenantid');
    if (empty($tenantid)) {
        return '';
    }

    // Determine if this page context is enabled.
    $pagecontexts = get_config('local_fastcomments', 'pagecontexts');
    if (empty($pagecontexts)) {
        return '';
    }

    $contextlevel = $PAGE->context->contextlevel;
    $enabledcontexts = explode(',', $pagecontexts);
    $scriptpath = $PAGE->url->get_path();

    $urlid = '';
    if ($contextlevel == CONTEXT_MODULE && in_array('module', $enabledcontexts)
            && preg_match('#/mod/\w+/view\.php$#', $scriptpath)) {
        // Only on module view pages (e.g. /mod/book/view.php).
        $urlid = 'moodle-cm-' . $PAGE->context->instanceid;
    } else if ($contextlevel == CONTEXT_COURSE && in_array('course', $enabledcontexts)
            && preg_match('#/course/view\.php$#', $scriptpath)) {
        // Only on /course/view.php, not participants/grades/etc.
        $urlid = 'moodle-course-' . $PAGE->context->instanceid;
    } else {
        return '';
    }

    $cdn = get_config('local_fastcomments', 'cdn');
    if (empty($cdn)) {
        $cdn = 'https://cdn.fastcomments.com';
    }

    // Build widget config.
    $config = [
        'tenantId' => $tenantid,
        'urlId'    => $urlid,
        'url'      => $PAGE->url->out(false),
    ];

    // Build SSO config.
    $ssotype = get_config('local_fastcomments', 'ssotype');
    if (empty($ssotype)) {
        $ssotype = 'secure';
    }

    $isloggedin = isloggedin() && !isguestuser();
    $loginurl = (new moodle_url('/login/index.php'))->out(false);
    $logouturl = (new moodle_url('/login/logout.php', ['sesskey' => sesskey()]))->out(false);

    if ($ssotype === 'secure') {
        $config['sso'] = local_fastcomments_build_secure_sso($isloggedin, $loginurl, $logouturl);
    } else if ($ssotype === 'simple') {
        $config['simpleSSO'] = local_fastcomments_build_simple_sso($isloggedin, $loginurl);
    }
    // ssotype === 'none': no SSO config added.

    $jsonconfig = json_encode($config, JSON_UNESCAPED_SLASHES);
    $urlid_js = addslashes($urlid);

    // Load the CDN embed script via Moodle's JS API.
    $PAGE->requires->js(new moodle_url($cdn . '/js/embed-v2.min.js'));

    // Register widget init code via Moodle's JS API.
    $PAGE->requires->js_init_code("
(function() {
    if (!window.fcInitializedById) {
        window.fcInitializedById = {};
    }
    if (window.fcInitializedById['{$urlid_js}']) {
        return;
    }
    window.fcInitializedById['{$urlid_js}'] = true;
    var attempts = 0;
    function attemptToLoad() {
        attempts++;
        if (attempts > 200) { return; }
        var widgetTarget = document.getElementById('fastcomments-widget');
        if (window.FastCommentsUI && widgetTarget) {
            window.FastCommentsUI(widgetTarget, {$jsonconfig});
            return;
        }
        setTimeout(attemptToLoad, attempts > 50 ? 500 : 50);
    }
    attemptToLoad();
})();
    ");

    return '<div id="fastcomments-widget"></div>';
}

/**
 * Build Secure SSO config using HMAC-SHA256.
 *
 * @param bool $isloggedin Whether the current user is logged in (not guest).
 * @param string $loginurl The Moodle login URL.
 * @param string $logouturl The Moodle logout URL.
 * @return array SSO config array.
 */
function local_fastcomments_build_secure_sso($isloggedin, $loginurl, $logouturl) {
    global $USER, $PAGE;

    $apisecret = get_config('local_fastcomments', 'apisecret');
    $timestamp = time() * 1000;

    $result = [
        'timestamp' => $timestamp,
        'loginURL'  => $loginurl,
        'logoutURL' => $logouturl,
    ];

    if ($isloggedin && !empty($apisecret)) {
        $ssouser = [
            'id'       => (string)$USER->id,
            'email'    => $USER->email,
            'username' => fullname($USER),
            'optedInNotifications' => (bool)get_user_preferences('local_fastcomments_optedinnotifications', 1),
            'optedInSubscriptionNotifications' => (bool)get_user_preferences(
                'local_fastcomments_optedinsubscriptionnotifications', 1
            ),
        ];

        // Get avatar URL.
        $userpicture = new user_picture($USER);
        $userpicture->size = 95;
        $avatarurl = $userpicture->get_url($PAGE)->out(false);
        if (!empty($avatarurl)) {
            $ssouser['avatar'] = $avatarurl;
        }

        // Check admin/moderator status.
        $ssouser['isAdmin'] = has_capability('moodle/site:config', \context_system::instance());

        $userdatajsonbase64 = base64_encode(json_encode($ssouser));
        $verificationhash = hash_hmac('sha256', $timestamp . $userdatajsonbase64, $apisecret);

        $result['userDataJSONBase64'] = $userdatajsonbase64;
        $result['verificationHash'] = $verificationhash;
    }

    return $result;
}

/**
 * Build Simple SSO config (client-side user data, no HMAC).
 *
 * @param bool $isloggedin Whether the current user is logged in (not guest).
 * @param string $loginurl The Moodle login URL.
 * @return array Simple SSO config array.
 */
function local_fastcomments_build_simple_sso($isloggedin, $loginurl) {
    global $USER, $PAGE;

    if (!$isloggedin) {
        return [
            'loginURL' => $loginurl,
        ];
    }

    $sso = [
        'username' => fullname($USER),
        'email'    => $USER->email,
    ];

    // Get avatar URL.
    $userpicture = new user_picture($USER);
    $userpicture->size = 95;
    $avatarurl = $userpicture->get_url($PAGE)->out(false);
    if (!empty($avatarurl)) {
        $sso['avatar'] = $avatarurl;
    }

    return $sso;
}

/**
 * Add FastComments notification preferences link to user profile navigation.
 *
 * @param \core_user\output\myprofile\tree $tree The profile navigation tree.
 * @param stdClass $user The user whose profile is being viewed.
 * @param bool $iscurrentuser Whether the profile belongs to the current user.
 * @param stdClass|null $course The current course, if any.
 */
function local_fastcomments_myprofile_navigation(
    \core_user\output\myprofile\tree $tree,
    $user,
    $iscurrentuser,
    $course
) {
    if (!$iscurrentuser) {
        return;
    }

    $category = new \core_user\output\myprofile\category(
        'fastcomments',
        get_string('pluginname', 'local_fastcomments'),
        'privacyandpolicies'
    );
    $tree->add_category($category);

    $tree->add_node(new \core_user\output\myprofile\node(
        'fastcomments',
        'preferences',
        get_string('preferences_link', 'local_fastcomments'),
        null,
        new moodle_url('/local/fastcomments/preferences.php')
    ));
}

/**
 * Declare user preferences used by this plugin.
 *
 * @return array[] Preference definitions keyed by preference name.
 */
function local_fastcomments_user_preferences() {
    $preferences = [];

    $preferences['local_fastcomments_optedinnotifications'] = [
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => 1,
        'choices' => [0, 1],
        'permissioncallback' => function ($user, $preferencename) {
            global $USER;
            return $user->id == $USER->id;
        },
    ];

    $preferences['local_fastcomments_optedinsubscriptionnotifications'] = [
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => 1,
        'choices' => [0, 1],
        'permissioncallback' => function ($user, $preferencename) {
            global $USER;
            return $user->id == $USER->id;
        },
    ];

    return $preferences;
}
