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
 * @package    theme_remui
 * @copyright  (c) 2018 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_remui_child;

defined('MOODLE_INTERNAL') || die();


/**
 * General remui utility functions.
 *
 * Added to a class for the convenience of auto loading.
 *
 * @package   theme_remui
 * @copyright WisdmLabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility {

    // Get users current courses with end date - today is between start and end date
    // Add in check for site config 'coursegraceperiodafter'/ 'coursegraceperiodbefore' to match Moodle current list (Grace period for past courses)/ (Grace period for future courses)
    public static function get_current_courses_finite($userid) {
        global $DB, $CFG;
        $coursegraceperiodafter = $CFG->coursegraceperiodafter.' DAY';
        $coursegraceperiodbefore = $CFG->coursegraceperiodbefore.' DAY';
        $courses = enrol_get_users_courses($userid, true, '', 'id');
        if ($courses) {
          $currents = array();
          foreach ($courses as $course) {
              $currents[] .= $course->id;
          }
          $courseids = implode(',',$currents);
          $sql = 'SELECT c.id, c.fullname, c.shortname, from_unixtime(c.enddate, "%D %M %Y") as enddate
             FROM {course} c
             WHERE c.id IN ('.$courseids.')
             AND c.visible = "1"
             AND c.startdate <= UNIX_TIMESTAMP(NOW() + INTERVAL '.$coursegraceperiodbefore.')
             AND c.enddate >= UNIX_TIMESTAMP(NOW() - INTERVAL '.$coursegraceperiodafter.')
             ORDER BY c.id ASC';
          $courses = $DB->get_records_sql($sql);
          if ($courses) {
              return $courses;
          }
        }
        return array();
    }

    // Get users current courses without end date - today is after start date
    public static function get_current_courses_persistent($userid) {
        global $DB;
        $courses = enrol_get_users_courses($userid, true, '', 'fullname');
        if ($courses) {
          $currents = array();
          foreach ($courses as $course) {
              $currents[] .= $course->id;
          }
          $courseids = implode(',',$currents);
          $sql = 'SELECT c.id, c.fullname, c.shortname
           FROM {course} c
           WHERE c.id IN ('.$courseids.')
           AND c.visible = "1"
           AND c.startdate <= UNIX_TIMESTAMP(NOW()- INTERVAL 1 HOUR)
           AND c.enddate = "0"
           ORDER BY c.fullname ASC';
          $courses = $DB->get_records_sql($sql);
          if ($courses) {
              return $courses;
          }
        }
        return array();
    }

    // Get recent courses accessed by user
    public static function get_recent_accessed_courses($userid) {
        global $USER, $DB;
        $recent_int = get_config('theme_remui_child', 'recentlimit_int');
        $recent_per = get_config('theme_remui_child', 'recentlimit_per');
        $limit = get_config('theme_remui_child','recentlimit');
        if ($recent_int && $recent_per) {
            $interval = $recent_int.' '.$recent_per;
        } else {
            $interval = '2 WEEK';
        }
        if (!$limit) {
            $limit = '5';
        }
        $sql = 'SELECT c.id, c.fullname, c.shortname, CASE
            WHEN c.enddate = "0" THEN "No ending date"
          ELSE
             CONCAT("End date: ", from_unixtime(c.enddate, "%D %M %Y"))
          END as enddate
               FROM {user_lastaccess} ul
               JOIN {course} c ON c.id = ul.courseid
               WHERE userid = ?
               AND FROM_UNIXTIME(timeaccess) >= DATE_SUB(NOW(), INTERVAL '.$interval.')
               ORDER BY timeaccess DESC 
               LIMIT '.$limit;

        if (!$userid) {
          $userid = $USER->id;
        }

        $params = array('userid'=> $userid);
        $courses = $DB->get_records_sql($sql, $params);
        if ($courses) {
            return $courses;
        }
        return array();
    }

    // Get starred courses accessed by user
    public static function get_starred_courses($userid) {
        global $USER, $DB;
        $sql = 'SELECT f.itemid
           FROM {favourite} f
           WHERE userid = ?
           AND itemtype = ?';

        $params = array('userid'=>$userid, 'itemtype'=>'courses');
        $favourites = $DB->get_records_sql($sql, $params);
        if ($favourites) {
          $faves = array();
          foreach ($favourites as $favourite) {
            $faves[] .= $favourite->itemid;
          }
          $favourites = implode(',',$faves);

          $sql = 'SELECT c.id, c.fullname, c.shortname, 
          CASE
            WHEN c.enddate = "0" THEN "No ending date"
          ELSE
             CONCAT("End date: ", from_unixtime(c.enddate, "%D %M %Y"))
          END as enddate
             FROM {course} c
             WHERE c.id IN ('.$favourites.')
             ORDER BY c.fullname ASC';
          $courses = $DB->get_records_sql($sql);
          if ($courses) {
              return $courses;
          }
        }
        return array();
    }

    /**
     * Return the recent blog.
     *
     * This function helps in retrieving the recent blog.
     *
     * @param int $start how many blog should be skipped if specified 0 no recent blog will be skipped.
     * @param int $blogcount number of blog to be return.
     * @return array $blog returns array of blog data.
     */
    public static function get_recent_blogs($start = 0, $blogcount = 10) {
        global $CFG;

        require_once($CFG->dirroot.'/blog/locallib.php');
        $bloglisting = new \blog_listing();

        $blogentries = $bloglisting->get_entries($start, $blogcount);

        foreach ($blogentries as $blogentry) {
            $blogsummary = strip_tags($blogentry->summary);
            $summarystring = strlen($blogsummary) > 150 ? substr($blogsummary, 0, 150)."..." : $blogsummary;
            $blogentry->summary = $summarystring;

            // Created at.
            $blogentry->createdat = date('d M, Y', $blogentry->created);

            // Link.
            $blogentry->link = $CFG->wwwroot.'/blog/index.php?entryid='.$blogentry->id;
        }
        return $blogentries;
    }

    public static function freshstart($courseid) {
        global $DB;

        $count = $DB->count_records('course_modules', array('course'=>$courseid,'deletioninprogress'=>'0'));
        if ($count <= 1) {
            return true;
        } else {
            return false;
        }
    }

}
