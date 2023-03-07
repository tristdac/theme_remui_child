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
 * Edwiser RemUI
 * @package    theme_remui_child
 * @copyright  (c) 2020 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/theme/remui_child/lib.php');
// use \theme_remui_child\admin_settingspage_tabs;
$em = get_site_enrolment_methods();

if ($ADMIN->fulltree) {
    $settings = new theme_remui_child_admin_settingspage_tabs('themesettingremui_child', get_string('configtitle', 'theme_remui_child'));

    // General settings
    $page = new admin_settingpage('theme_remui_child_general', get_string('generalsettings', 'theme_remui'));

    $page->add(new admin_setting_heading('theme_remui_child_course_header', get_string('course_header', 'theme_remui_child'),format_text('', FORMAT_MARKDOWN)));

    $name = 'theme_remui_child/enableframeworks';
    $title = get_string('enableframeworks', 'theme_remui_child');
    $description = get_string('enableframeworksdesc', 'theme_remui_child');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_remui_child/curriculumidentifier';
    $title = get_string('curriculumidentifier', 'theme_remui_child');
    $description = get_string('curriculumidentifierdesc', 'theme_remui_child');
    $default = 'collegedatabase';
    $choices = $em;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    $name = 'theme_remui_child/enableteams';
    $title = get_string('enableteams', 'theme_remui_child');
    $description = get_string('enableteamsdesc', 'theme_remui_child');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_remui_child/leadtime';
    $title = get_string('leadtime', 'theme_remui_child');
    $description = get_string('leadtime_desc', 'theme_remui_child');
    $default = '2';
     $setting = new admin_setting_configtext($name, $title, $description, $default);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $page->add(new admin_setting_heading('theme_remui_child_quickcourse', get_string('quickcourse', 'theme_remui_child'),format_text('', FORMAT_MARKDOWN)));

    $name = 'theme_remui_child/recentlimit';
    $title = get_string('recentlimit', 'theme_remui_child');
    $description = get_string('recentlimit_desc', 'theme_remui_child');
    $default = '5';
     $setting = new admin_setting_configtext($name, $title, $description, $default);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_remui_child/recentlimit_int';
    $title = get_string('recentlimit_int', 'theme_remui_child');
    $description = get_string('recentlimit_int_desc', 'theme_remui_child');
    $range = range(1,15);
    array_unshift($range, null);
    unset($range[0]);
    $setting = new admin_setting_configselect($name, $title, $description, 2, $range);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_remui_child/recentlimit_per';
    $title = get_string('recentlimit_per', 'theme_remui_child');
    $description = get_string('recentlimit_per_desc', 'theme_remui_child');
    $setting = new admin_setting_configselect(
        $name,
        $title,
        $description,
        'WEEK',
        array(
            'MINUTE' => 'Minutes',
            'HOUR' => 'Hours',
            'DAY' => 'Days',
            'WEEK' => 'Weeks'
        )
    );
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_remui_child/includepersistent';
    $title = get_string('includepersistent', 'theme_remui_child');
    $description = get_string('includepersistentdesc', 'theme_remui_child');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $page->add(new admin_setting_heading('theme_remui_child_coursecat', get_string('coursecat', 'theme_remui_child'),format_text('', FORMAT_MARKDOWN)));

    // Active course category on course archive page.
    $name = 'theme_remui_child/categoryid';
    $title = get_string('categoryid', 'theme_remui_child');
    $description = get_string('categoryiddesc', 'theme_remui_child');
    $default = 'all';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $page->add($setting);

    $page->add(new admin_setting_heading('theme_remui_child_login', get_string('login_options', 'theme_remui_child'),format_text('', FORMAT_MARKDOWN)));

    $name = 'theme_remui_child/prioritise_oidc';
    $title = get_string('prioritise_oidc', 'theme_remui_child');
    $description = get_string('prioritise_oidc_desc', 'theme_remui_child');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    $page = new admin_settingpage('theme_remui_child_announcements', get_string('announcements', 'theme_remui_child'));

    $name = 'theme_remui_child/enableannouncements';
    $title = get_string('enableannouncement', 'theme_remui');
    $description = get_string('enableannouncementdesc', 'theme_remui');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    $remuisettings['enableannouncement'] = [[
        'value'  => true,
        'show' => ['announcementtext', 'announcementtype'],
    ], [
        'value'  => false,
        'hide' => ['announcementtext', 'announcementtype'],
    ]];

    $name = 'theme_remui_child/announcementcount';
    $title = get_string('announcementcount', 'theme_remui_child');
    $description = get_string('announcementcount_desc', 'theme_remui_child');
    $setting = new admin_setting_configselect(
        $name,
        $title,
        $description,
        'WEEK',
        range(1,5)
    );
    // $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $announcementsenabled = get_config('theme_remui_child','enableannouncements');
    $announcementcount = get_config('theme_remui_child','announcementcount')+1;
    
    foreach(range(1,$announcementcount) as $n) {
        $page->add(new admin_setting_heading('theme_remui_child_announcement'.$n,get_string('announcementtext', 'theme_remui'). ' '.$n,format_text('', FORMAT_MARKDOWN)));
        // Announcment text.
        $name = 'theme_remui_child/announcementtext'.$n;
        $title = get_string('announcementtext', 'theme_remui'). ' '.$n;
        $description = get_string('announcementtextdesc', 'theme_remui');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        // $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_remui_child/targetaudience'.$n;
        $title = get_string('targetaudience', 'theme_remui_child'). ' '.$n;
        $description = get_string('targetaudiencedesc', 'theme_remui_child');
        $default = 'All Users';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_remui_child/announcementtype'.$n;
        $title = get_string('announcementtype', 'theme_remui'). ' '.$n;
        $description = get_string('announcementtypedesc', 'theme_remui');
        $setting = new admin_setting_configselect(
            $name,
            $title,
            $description,
            1,
            array(
            'info'    => get_string('typeinfo', 'theme_remui'),
            'success' => get_string('typesuccess', 'theme_remui'),
            'warning' => get_string('typewarning', 'theme_remui'),
            'danger'  => get_string('typedanger', 'theme_remui')
            )
        );
        $page->add($setting);
    }
    $settings->add($page);
}
if (optional_param('section', '', PARAM_TEXT) == 'themesettingremui') {
    global $PAGE;
    $PAGE->requires->data_for_js('remuisettings', $remuisettings);
    $PAGE->requires->js(new moodle_url('/theme/remui/settings.js'));
    $PAGE->requires->js_call_amd('theme_remui/settings', 'init');
}