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
 * A two column layout for the remui theme.
 *
 * @package   theme_remui
 * @copyright 2016 Damyon Wiese
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->libdir . '/completionlib.php');

use \theme_remui\toolbox;

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('pin_aside', PARAM_ALPHA);
user_preference_allow_ajax_update('course_view_state', PARAM_ALPHA);
user_preference_allow_ajax_update('enable_focus_mode', PARAM_BOOL);
user_preference_allow_ajax_update('remui_dismised_announcement', PARAM_BOOL);
user_preference_allow_ajax_update('edwiser_inproduct_notification', PARAM_ALPHA);
user_preference_allow_ajax_update('qcl_view_state', PARAM_ALPHA);

global $PAGE, $CFG, $COURSE;

$PAGE->requires->strings_for_js(['sidebarpinned', 'sidebarunpinned', 'pinsidebar', 'unpinsidebar', 'changelog'], 'theme_remui');

// RemUI Usage Tracking (RemUI Analytics).
$ranalytics = new \theme_remui\usage_tracking();
$ranalytics->send_usage_analytics();


// Main content Top Region
$topblocks = $OUTPUT->blocks('side-top');
$hastopblocks = strpos($topblocks, 'data-block=') !== false;

// Main content Bottom Region
$bottomblocks = $OUTPUT->blocks('side-bottom');
$hasbottomblocks = strpos($bottomblocks, 'data-block=') !== false;

// Page aside blocks
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    $rightsidebar = (get_user_preferences('pin_aside', 'true') == 'true');
    // Always pinned for quiz and book activity.
    $activities = array("book", "quiz");
    if (isset($PAGE->cm->id) && in_array($PAGE->cm->modname, $activities) || $PAGE->user_is_editing()) {
        $rightsidebar = true;
        $navdraweropen = false;
    }
} else if ($hasblocks) {
    $navdraweropen = false;
    $rightsidebar = true;
} else {
    $navdraweropen = false;
    $rightsidebar = true;
}

// Message drawer html and drawer toggle in sidebar tabs.
$mergemessagingsidebar = \theme_remui\toolbox::get_setting('mergemessagingsidebar');
$messagedrawer = '';
$messagetoggle = '';
if ($mergemessagingsidebar) {
    $messagedrawer = core_message_standard_after_main_region_html();
    $messagetoggle = \theme_remui\usercontroller::render_navbar_output();
}
$unreadrequestcount = 0;
if ($messagetoggle) {
    $unreadcount = \core_message\api::count_unread_conversations($USER);
    $requestcount = \core_message\api::get_received_contact_requests_count($USER->id);
    $unreadrequestcount = $unreadcount + $requestcount;
}
$usercanmanage = \theme_remui\utility::check_user_admin_cap();
$initrightsidebar = false;
$hasmessaging = empty($messagedrawer) !== true;

$extraclasses = ['remui-customizer'];

if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
if ($PAGE->pagelayout == 'mypublic') {
    $extraclasses [] = ' page-profile ';
}
if ($hasblocks) {
    $extraclasses[] = 'hasblocks';
    if ($rightsidebar) {
        $extraclasses[] = 'sidebar-pinned';
    }
} else {
    $messagetoggle = str_replace(
        'class="nav-link popover-region-toggle p-3"',
        'class="nav-link popover-region-toggle active show p-3"',
        $messagetoggle
    );
}

$qcl_view = get_user_preferences('qcl_view_state');
$qcl_view_current = false;
$qcl_view_recent = false;
$qcl_view_starred = false;

if ($qcl_view === 'current') {
    $extraclasses[] = 'qcl_view_current';
    $qcl_view_current = true;
}
if ($qcl_view === 'recent') {
    $extraclasses[] = 'qcl_view_recent';
    $qcl_view_recent = true;
}
if ($qcl_view === 'starred') {
    $extraclasses[] = 'qcl_view_starred';
    $qcl_view_starred = true;
}

if ($hasblocks || $usercanmanage || $hasmessaging) {
    $initrightsidebar = true;
}

if ($mergemessagingsidebar) {
    $extraclasses[] = 'mergemessagingsidebar';
}

// Focus Mode Code
$focusdata = [];
if (($PAGE->pagelayout === 'course' || $PAGE->pagelayout === 'incourse') && $PAGE->pagetype !== "enrol-index") {
    $focusdata['enabled'] = \theme_remui\toolbox::get_setting('enablefocusmode');
    $focusdata['on'] = get_user_preferences('enable_focus_mode', false) && $focusdata['enabled'];
    if ($focusdata['on']) {
        $extraclasses[] = 'focusmode';
        $focusdata['btnbg'] = 'btn-danger';
        $focusdata['btnicon'] = 'fa-compress';
    } else {
        $focusdata['btnbg'] = 'btn-primary';
        $focusdata['btnicon'] = 'fa-expand';
    }
    $focusdata['coursename'] = $COURSE->fullname;
    if ($PAGE->pagelayout === 'incourse') {
        $focusdata['courseurl'] = $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id;
    }

    $coursecontext = context_course::instance($COURSE->id);

    if (is_enrolled($coursecontext, $USER->id)) {
        $completion = new \completion_info($COURSE);
        if ($completion->is_enabled()) {
            $percentage = \core_completion\progress::get_course_progress_percentage($COURSE, $USER->id);
            if ($percentage === null) {
                $percentage = 0;
            }
            $focusdata['progress'] = (int)$percentage;
        }
    }
}

/*Enrolment Page setup*/
$enrolconfig = get_config('theme_remui', 'enrolment_page_layout');
if ($PAGE->pagetype == "enrol-index" && $enrolconfig == "1") {

    $templatecontext['enableenrollayout'] = $enrolconfig;
    $extraclasses[] = 'enableenrollayout';
}

/*Course Archive Page setup*/
$pagelayout = get_config('theme_remui', 'categorypagelayout');
if ($PAGE->pagelayout == "coursecategory" && $pagelayout !== "0") {
    $extraclasses[] = 'category-layout'.$pagelayout;
}

$customizer = \theme_remui\customizer\customizer::instance();
$extraclasses[] = 'header-site-identity-' . $customizer->get_config('logoorsitename');
$extraclasses[] = 'header-primary-layout-desktop-' . $customizer->get_config('header-primary-layout-desktop');
$extraclasses[] = 'header-primary-layout-mobile-' . $customizer->get_config('header-primary-layout-mobile');

$icondesign = \theme_remui\toolbox::get_setting('icondesign');
if ($icondesign !== 'default') {
    $extraclasses[] = $icondesign;
}
$formgroupdesign = \theme_remui\toolbox::get_setting('formgroupdesign');
if ($formgroupdesign !== 'default') {
    $extraclasses[] = $formgroupdesign;
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);

$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$sitecolor = get_config('theme_remui_child', 'sitecolor');
$sitecolor = ($sitecolor == "") ? 'primary' : $sitecolor;
$sitecolorhex = get_config('theme_remui_child', 'sitecolorhex');
if (stripos($sitecolorhex, '#') === false) {
    $sitecolorhex = '#'.$sitecolorhex;
}
$navbarinverse = get_config('theme_remui_child', 'navbarinverse');
$sidebarcolor = get_config('theme_remui_child', 'sidebarcolor');
$lcontroller = new \theme_remui\controller\LicenseController();
// QuickCourse definitions
$recent_int = get_config('theme_remui_child', 'recentlimit_int');
$recent_per = get_config('theme_remui_child', 'recentlimit_per');
$includepersistent = get_config('theme_remui_child', 'includepersistent');

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'sidetopblocks' => $topblocks,
    'hastopblocks' => $hastopblocks,
    'sidebottomblocks' => $bottomblocks,
    'hasbottomblocks' => $hasbottomblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'cansignup' => (!empty($CFG->registerauth) && (!isloggedin() || isguestuser())),
    'signupurl' => new moodle_url('/login/signup.php'),
    'footerdata' => \theme_remui\utility::get_footer_data(),
    'usercanmanage' => $usercanmanage,
    'messagedrawer' => $messagedrawer,
    'initrightsidebar' => $initrightsidebar,
    'messagetoggle' => $messagetoggle,
    'navbarinverse' => $navbarinverse,
    'sidebarcolor' => $sidebarcolor,
    'unreadrequestcount' => $unreadrequestcount,
    'pinaside' => $rightsidebar,
    $sitecolor => true,
    'sitecolorhex' => $sitecolorhex,
    'focusdata' => $focusdata,
    'cansendfeedback' => (is_siteadmin()) ? true : false, // check to show feedback button if admin user
    'feedbacksender_emailid' => isset($USER->email) ? $USER->email : '', // email id of the person sending feedback, for auto fill the email field in feedback overview modal
    'feedback_loading_image' => $OUTPUT->image_url('a/loading', 'core'),
    'licensestatus_forfeedback' => ($lcontroller->get_data_from_db() == 'available') ? 1 : 0,
    'qcl_view' => $qcl_view,
    'qcl_view_current' => $qcl_view_current,
    'qcl_view_recent' => $qcl_view_recent,
    'qcl_view_starred' => $qcl_view_starred,
    'hascurrent_f' => false,
    'hascurrent_p' => false,
    'hascurrent' => false,
    'hasrecent' => false,
    'hasstarred' => false,
    'hassomething' => false,'recent_value' => $recent_int,
    'recent_scope' => strtolower($recent_per)."s",
];

if (isset($fullwidthenrol)) {
    $templatecontext['fullwidthenrol'] = $fullwidthenrol;
}

$flatnavigation = $PAGE->flatnav;

$templatecontext['flatnavigation'] = $flatnavigation;
$templatecontext['firstcollectionlabel'] = $flatnavigation->get_collectionlabel();
$templatecontext['navfootermenu'] = \theme_remui\utility::get_left_nav_footer_menus();


// QuickCourse
if (isloggedin() && \theme_remui\toolbox::get_setting('enablerecentcourses')) {
    $courses_f = \theme_remui_child\utility::get_current_courses_finite($USER->id);
    $finalarr_f = array();
    foreach ($courses_f as $key => $course_f) {
        $templatecontext['hascurrent'] = true;
        $templatecontext['hascurrent_f'] = true;
        $finalarr_f[] = array (
            'id' => $course_f->id,
            'fullname' => format_text($course_f->fullname),
            'shortname' => format_text($course_f->shortname),
            'enddate' => $course_f->enddate
        );
    }
    $templatecontext['currentcourses_finite'] = $finalarr_f;
    if ($includepersistent === '1') {
        $courses_p = \theme_remui_child\utility::get_current_courses_persistent($USER->id);
        $finalarr_p = array();
        foreach ($courses_p as $key => $course_p) {
            $templatecontext['hascurrent'] = true;
            $templatecontext['hascurrent_p'] = true;
            $finalarr_p[] = array (
                'id' => $course_p->id,
                'fullname' => format_text($course_p->fullname),
                'shortname' => format_text($course_p->shortname),
                'enddate' => 'No end date'
            );
        }
        $templatecontext['currentcourses_persistent'] = $finalarr_p;
    }

    $courses_r = \theme_remui_child\utility::get_recent_accessed_courses($USER->id);
    $finalarr = array();
    foreach ($courses_r as $key => $course_r) {
        $templatecontext['hasrecent'] = true;
        $finalarr[] = array (
            'id' => $course_r->id,
            'fullname' => format_text($course_r->fullname),
            'shortname' => format_text($course_r->shortname),
            'enddate' => $course_r->enddate
        );
    }
    $templatecontext['recentcourses'] = $finalarr;

    $courses_s = \theme_remui_child\utility::get_starred_courses($USER->id);
    $finalarr = array();
    foreach ($courses_s as $key => $course_s) {
        $templatecontext['hasstarred'] = true;
        $finalarr[] = array (
            'id' => $course_s->id,
            'fullname' => format_text($course_s->fullname),
            'shortname' => format_text($course_s->shortname),
            'enddate' => $course_s->enddate
        );
    }
    $templatecontext['starredcourses'] = $finalarr;
}

if ($templatecontext['hasrecent'] === true || $templatecontext['hasstarred'] === true || $templatecontext['hascurrent'] === true) {
    $templatecontext['hassomething'] = true;
}

$templatecontext['enabledictionary'] = \theme_remui\toolbox::get_setting('enabledictionary');
if (strpos($bodyattributes, 'editing') !== false) {
    $templatecontext['editingenabled'] = true;
}

if (get_user_preferences('course_cache_reset')) {
    $coursehandler = new \theme_remui_coursehandler();
    $coursehandler->invalidate_course_cache();
} else if (get_config('theme_remui', 'cache_reset_time') > get_user_preferences('cache_reset_time')) {
    $coursehandler = new \theme_remui_coursehandler();
    $coursehandler->invalidate_course_cache();
}

// Setup Wizard Prompt
if (is_siteadmin()) {
    $swflag = get_config('theme_remui', 'flagsetupwizard');

    if (!$swflag) {
        $templatecontext['promptsetupwizard'] = true;
    }
}

// feedback JS needs this URL to funtion properly.
$canvasurl = new moodle_url($CFG->wwwroot . '/theme/remui/amd/src/html2canvas.js');
$templatecontext['canvasurl'] = $canvasurl->__toString();

// Unset the EDD_LICENSE_ACTION i.e. license notice- for activated themes.
toolbox::remove_plugin_config(EDD_LICENSE_ACTION);

// Custom Modal Generation code
if ($PAGE->user_is_editing() && is_plugin_available('local_edwiserpagebuilder')) {
    $cm = new \local_edwiserpagebuilder\content_manager();
    $templatecontext['epb_add_blocks'] = $cm->generate_add_block_modal();
    $PAGE->requires->js_call_amd('local_edwiserpagebuilder/blockmanager', 'load');

    // No need to add for other themes, Only required in Theme RemUI.
    // Because we have floating buttons in our theme to enable and disable the editing on
    // frontpage.
    if ($PAGE->pagelayout == "frontpage" && \theme_remui\toolbox::get_setting('frontpagechooser') != 1) {
        $templatecontext['addablock'] = $cm->create_floating_add_a_block_button();
    }
}

// Init product notification configuration
$notification = get_user_preferences('edwiser_inproduct_notification');

if ($notification != null && $notification != "false" && $notification != false) {

    $notification = json_decode($notification);
    
    $templatecontext['notification'] = [
        "msg" => get_string($notification->msg, "theme_remui", $notification->param),
        "imgclass" => $notification->img,
        "edwiserlogo" => $OUTPUT->image_url('edwiser-logo', 'theme_remui')->__toString(),
        "mainimg" => $OUTPUT->image_url($notification->img, 'theme_remui')->__toString()
    ];
}

