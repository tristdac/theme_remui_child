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
 * Edwiser RemUI Version
 * @package    theme_remui_child
 * @copyright  (c) 2018 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']='Edwiser RemUI Child';
$string['choosereadme']='Theme RemUI is a child theme of Boost.';
$string['configtitle'] = 'Edwiser RemUI Child';
$string['due'] = 'Due {$a}';
$string['xofyanswered'] = '{$a->completed} of {$a->participants} Answered';
$string['xofyattempted'] = '{$a->completed} of {$a->participants} Attempted';
$string['xofycontributed'] = '{$a->completed} of {$a->participants} Contributed';
$string['xofysubmitted'] = '{$a->completed} of {$a->participants} Submitted';
$string['xungraded'] = '{$a} Ungraded';
$string['feedbackavailable'] = 'Feedback available';
$string['submitted'] = 'Submitted';
$string['notsubmitted'] = 'Not Submitted';
$string['draft'] = 'Not published to students';
$string['attempted'] = 'Attempted';
$string['notattempted'] = 'Not Attempted';
$string['overdue'] = 'Overdue';
$string['reopened'] = 'Reopened';
$string['contributed'] = 'Contributed';
$string['notcontributed'] = 'Not contributed';
$string['recentlimit'] = 'Recently Accessed Courses';
$string['recentlimit_desc'] = 'How many courses (maximum) to show in the recent courses dropdown';
$string['recentlimit_int'] = 'Recent Limit Number';
$string['recentlimit_int_desc'] = 'An integer number to limit the recent courses list by. ie. within the past X hours/days/weeks';
$string['recentlimit_per'] = 'Recent Limit Period';
$string['recentlimit_per_desc'] = 'The period in which limit the recent courses list by. ie. within the past X hours/days/weeks';
$string['quickcourse'] = 'QuickCourse';
$string['course_header'] = 'Course Header Features';
$string['coursecat'] = 'Course Archive Page Settings';
$string['enableframeworks'] = 'Enable Course Header Frameworks';
$string['enableframeworksdesc'] = 'Show framework links, unit links and course published status in course headers';
$string['enableteams'] = 'Enable Course Header Teams Links';
$string['enableteamsdesc'] = 'Add MS Teams links to course headers. Allows teachers to request creation and provides link to teams once created.';
$string['leadtime'] = 'Lead Time';
$string['leadtime_desc'] = 'We need to specify the maximum number of days to allow for admins to create a requested Team in order to inform the requestor.';
$string['curriculumidentifier'] = 'Enrolment Method Identifier';
$string['curriculumidentifierdesc'] = 'Identify a curriculum course by the selected enrolment method (normally database enrolment). This is used to determine which courses the header framework functionality appears on.';
$string['categoryid'] = 'Default active category ID';
$string['categoryiddesc'] = 'The category ID of the category which should be active by default when visiting the course archive (index) page';
$string['announcementcount'] = 'Number of announcements';
$string['announcementcount_desc'] = 'How many site announcements would you like to configure and enable?';
$string['targetaudience'] = 'Target Audience';
$string['targetaudiencedesc'] = 'Who is this announcement targeted at? Enter profile field value for "User Type" eg. "Staff", "Student", "External". If "All Users" or empty, it is shown to all users.';
$string['announcements'] = 'Site Announcements';
$string['current'] = 'Current Courses';
$string['starred'] = 'Starred Courses';
$string['recent'] = 'Recent Courses';
$string['includepersistent'] = 'Include Persistent Courses';
$string['others'] = 'Non-scheduled Courses';
$string['includepersistentdesc'] = 'If enabled, courses with no end date will be included - These will most likely be non academic courses like Health & Safety training or curriculum Hubs';
$string['nofaves'] = '<p>You must have at least one favourite course before anything shows in this tab.</p><p>You can visit your Dashboard o star courses.</p>';
$string['nocurrs'] = '<p>You do not have any current courses. Go to your Dashboard to view older courses.</p>';
$leadtime = get_config('theme_remui_child','leadtime');
// $string['team_requested'] = '<h3>Team requested!</h3> Please allow up to '.$leadtime.' working days.  You will receive an email notification once the Team has been created';
$string['team_requested'] ='<h3>Team requested!</h3> Your team will created soon. You will receive a notification from Microsoft Teams once the Team has been created';
$string['event_team_requested'] = 'MS Team Requested';
$string['quickcourse'] = 'QuickCourse';
$string['loginhelp'] = 'Login Help';
$string['noneclogin'] = "If you are not an employee or student of Edinburgh College or do not have an @edinburghcollege.ac.uk email address, please login below. Enter your supplied username and password followed by the 'Log in' button.";
$string['potentialidps'] = 'Log in using your Edinburgh College Microsoft 365 account:';
$string['manuallogin'] = "Don't have an Edinburgh College account?";
$string['login_options'] = 'Login page options';
$string['prioritise_oidc'] = 'Prioritise OIDC Authentication on Login';
$string['prioritise_oidc_desc'] = 'Enabling will move OIDC method to top of form and place manual login form into modal as a secondary method';
$string['sitenot365connected'] = 'This site is integrated with Office 365. You must connect your Moodle account to Office 365 in order to benefit from these features.';
$string['coursenot365connected'] = 'This course has an associated Microsoft Team, but you are missing out as you are not yet connected to Office 365.';