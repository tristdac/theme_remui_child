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
 * Edwiser RemUI Course Renderer Class
 * @package   theme_remui
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_remui\output\core;
defined('MOODLE_INTERNAL') || die();

use moodle_url;
use coursecat_helper;
use lang_string;
use core_course_category;
use context_system;
use html_writer;
use core_text;
use pix_icon;
use cm_info;
use context_course;
use course_in_list;
use stdClass;
use renderable;
use action_link;

require_once($CFG->dirroot . '/course/renderer.php');
require_once($CFG->dirroot . '/mod/assign/renderer.php');

/**
 * Edwiser RemUI Course Renderer Class.
 *
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \core_course_renderer {

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new \completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('id'=> 'Section-'.$section->id, 'class' => 'section img-text', 'role'=>'region', 'aria-labelledby'=>'Sectionid-'.$section->id));

        return $output;
    }

    // New methods added for activity styling below.  Adapted from snap theme by Moodleroooms.
    /**
     * Overridden.  Customise display.  Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        global $PAGE;
        $output = '';
        // We return empty string (because course module will not be displayed at all) if
        // 1) The activity is not visible to users and
        // 2) The 'availableinfo' is empty, i.e. the activity was hidden in a way that leaves no info, such as using the
        // eye icon.
        if ( (method_exists($mod, 'is_visible_on_course_page')) && (!$mod->is_visible_on_course_page())
                || (!$mod->uservisible && empty($mod->availableinfo)) ) {
            return $output;
        }
        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }
        $output .= html_writer::start_tag('div');
        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }
        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));
        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);
        // Start a wrapper for the actual content to keep the indentation consistent.
        $output .= html_writer::start_tag('div', array('class' => 'activity-wrapper'));
        // Display the link to the module (or do nothing if module has no url).
        $cmname = $this->course_section_cm_name($mod, $displayoptions);
        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            $output .= $cmname;
            // Module can put text after the link (e.g. forum unread).
            $output .= $mod->afterlink;
            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // .activityinstance class.
        }
        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case icons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }
        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }
        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);
        if (!empty($modicons)) {
            $output .= html_writer::start_tag('div', array('class' => 'actions-right'));
            $output .= html_writer::span($modicons, 'actions');
            $output .= html_writer::end_tag('div');
        }
        // Get further information.
        $hasmeta = $this->course_section_cm_get_meta($mod);
        if ($hasmeta) {
            $output .= html_writer::start_tag('div', array('class' => 'activity-meta-container'));
            $output .= $this->course_section_cm_get_meta($mod);
            $output .= html_writer::end_tag('div');
            // TO BE DELETED    $output .= '<div style="clear: both;"></div>'; ????
        }
        // If there is content AND a link, then display the content here.
        // (AFTER any icons). Otherwise it was displayed before.
        if (!empty($url)) {
            $output .= $contentpart;
        }
        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);
        $output .= html_writer::end_tag('div');
        // End of indentation div.
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $output;
    }
    /**
     * Get the module meta data for a specific module.
     *
     * @param cm_info $mod
     * @return string
     */
    protected function course_section_cm_get_meta(cm_info $mod) {
        global $COURSE, $OUTPUT;
        $content = '';
        if (is_guest(context_course::instance($COURSE->id))) {
            return '';
        }
        // Do we have an activity function for this module for returning meta data?
        $meta = \theme_remui_child\activity::module_meta($mod);
        if (!$meta->is_set(true)) {
            // Can't get meta data for this module.
            return '';
        }
        $content .= '';
        $duedate = '';
        $warningclass = '';
        if ($meta->submitted) {
            $warningclass = ' activity-date-submitted ';
        }
        $activitycontent = $this->submission_cta($mod, $meta);
        if (!(empty($activitycontent))) {
            if ( ($mod->modname == 'assign') && ($meta->submitted) ) {
                $content .= html_writer::start_tag('span', array('class' => ' activity-due-date ' . $warningclass));
                $content .= $activitycontent;
                $content .= html_writer::end_tag('span') . '<br>';
            } else {
                // Only display if this is really a student on the course (i.e. not anyone who can grade an assignment).
                if (!has_capability('mod/assign:grade', $mod->context)) {
                    $content .= html_writer::start_tag('div', array('class' => 'activity-mod-engagement' . $warningclass));
                    $content .= $activitycontent;
                    $content .= html_writer::end_tag('div');
                }
            }
        }
        // Activity due date.
        if (!empty($meta->extension) || !empty($meta->timeclose)) {
            $due = get_string('due', 'theme_remui_child');
            if (!empty($meta->extension)) {
                $field = 'extension';
            } else if (!empty($meta->timeclose)) {
                $field = 'timeclose';
            }
            $pastdue = $meta->$field < time();
            // Create URL for due date.
            $url = new \moodle_url("/mod/{$mod->modname}/view.php", ['id' => $mod->id]);
            $dateformat = get_string('strftimedate', 'langconfig');
            $labeltext = get_string('due', 'theme_remui_child', userdate($meta->$field, $dateformat));
            $warningclass = '';
            // Display assignment status (due, nearly due, overdue), as long as it hasn't been submitted,
            // or submission not required.
            if ( (!$meta->submitted) && (!$meta->submissionnotrequired) ) {
                $warningclass = '';
                $labeltext = '';
                // If assignment due in 7 days or less, display in amber, if overdue, then in red, or if submitted, turn to green.
                // If assignment is 7 days before date due(nearly due).
                $timedue = $meta->$field - (86400 * 7);
                if ( (time() > $timedue) &&  !(time() > $meta->$field) ) {
                    if ($mod->modname == 'assign') {
                        $warningclass = ' activity-date-nearly-due';
                    }
                } else if (time() > $meta->$field) { // If assignment is actually overdue.
                    if ($mod->modname == 'assign') {
                            $warningclass = ' activity-date-overdue';
                    }
                    $labeltext .= html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-exclamation-circle')) . ' ';
                }
                $labeltext .= get_string('due', 'theme_remui_child', userdate($meta->$field, $dateformat));
                $activityclass = '';
                if ($mod->modname == 'assign') {
                        $activityclass = ' activity-due-date ';
                }
                $duedate .= html_writer::tag('span', $labeltext, array('class' => $activityclass . $warningclass));
            }
            $content .= html_writer::start_tag('div', array('class' => 'activity-mod-engagement'));
            $content .= $duedate . html_writer::end_tag('div');
        }
        if ($meta->isteacher) {
            // Teacher - useful teacher meta data.
            $engagementmeta = array();
            // Below, !== false means we get 0 out of x submissions.
            if (!$meta->submissionnotrequired && $meta->numsubmissions !== false) {
                $engagementmeta[] = get_string('xofy'.$meta->submitstrkey, 'theme_remui_child',
                        (object) array(
                                'completed' => $meta->numsubmissions,
                                'participants' => \theme_remui_child\utils::course_participant_count($COURSE->id, $mod->modname)
                        )
                        );
            }
            if ($meta->numrequiregrading) {
                $engagementmeta[] = get_string('xungraded', 'theme_remui_child', $meta->numrequiregrading);
            }
            if (!empty($engagementmeta)) {
                $engagementstr = implode(', ', $engagementmeta);
                $params = array(
                        'action' => 'grading',
                        'id' => $mod->id,
                        'tsort' => 'timesubmitted',
                        'filter' => 'require_grading'
                );
                $url = new moodle_url("/mod/{$mod->modname}/view.php", $params);
                $icon = html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-info-circle'));
                $content .= html_writer::start_tag('div', array('class' => 'activity-mod-engagement'));
                $content .= html_writer::tag('span', $icon . $engagementstr);
                $content .= html_writer::end_tag('div');
            }
        } else {
            // Feedback meta.
            if (!empty($meta->grade)) {
                   $url = new \moodle_url('/grade/report/user/index.php', ['id' => $COURSE->id]);
                if (in_array($mod->modname, ['quiz', 'assign'])) {
                    $url = new \moodle_url('/mod/'.$mod->modname.'/view.php?id='.$mod->id);
                }
                $content .= html_writer::start_tag('span', array('class' => 'activity-mod-feedback'));
                $feedbackavailable = html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-commenting-o')) .
                    get_string('feedbackavailable', 'theme_remui_child');
                $content .= html_writer::link($url, $feedbackavailable);
                $content .= html_writer::end_tag('span');
            }
            // If submissions are not allowed, return the content.
            if (!empty($meta->timeopen) && $meta->timeopen > time()) {
                // TODO - spit out a 'submissions allowed from' tag.
                return $content;
            }
        }
        return $content;
    }
    /**
     * Submission call to action.
     *
     * @param cm_info $mod
     * @param activity_meta $meta
     * @return string
     * @throws coding_exception
     */
    public function submission_cta(cm_info $mod, \theme_remui_child\activity_meta $meta) {
        global $CFG;
        if (empty($meta->submissionnotrequired)) {
            // $url = $CFG->wwwroot.'/mod/'.$mod->modname.'/view.php?id='.$mod->id;
            if ($meta->submitted) {
                if (empty($meta->timesubmitted)) {
                    $submittedonstr = '';
                } else {
                    $submittedonstr = ' '.userdate($meta->timesubmitted, get_string('strftimedate', 'langconfig'));
                }
                $message = html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-check')) . $meta->submittedstr.$submittedonstr;
            } else {
                $warningstr = $meta->draft ? $meta->draftstr : $meta->notsubmittedstr;
                $warningstr = $meta->reopened ? $meta->reopenedstr : $warningstr;
                $message = $warningstr;
                $message = html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-info-circle')) . $message;
            }
            return html_writer::tag('span', $message);
        }
        return '';
    }
}
