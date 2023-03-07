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
 * Edwiser RemUI Functions
 * @package    theme_remui
 * @copyright  (c) 2018 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function check_system_roles() {
	global $DB, $USER;
	$result = $DB->get_field('role_assignments', 'id', array('id' => $USER->id, 'contextid' => '1'), $strictness=IGNORE_MISSING);
	return $result;
}


function get_user_categories_role($unitid) {
	global $DB, $USER;
	$course_cat = $DB->get_field('course', 'category', array('id' => $unitid));
	$course_cats_path = $DB->get_field('course_categories', 'path', array('id' => $course_cat), $strictness=IGNORE_MISSING);
	$course_cats_arr = array_map('intval', explode('/', ltrim($course_cats_path,'/')));
	$course_cats_arr = implode(",",$course_cats_arr);
	
	$sql = "SELECT ctx.id
			FROM {role_assignments} ra
			LEFT JOIN {context} ctx
			ON ctx.id = ra.contextid 
			WHERE ctx.contextlevel = '40'
			AND ctx.instanceid IN ({$course_cats_arr})
			AND ra.userid = '{$USER->id}'";
	$cat_enrolment = $DB->get_record_sql($sql, array(), $strictness=IGNORE_MISSING);
	return $cat_enrolment;
}


function get_user_access($unitid) {
	global $USER;
	$context = context_course::instance($unitid);
	$enrolled = is_enrolled($context, $USER->id, '', true);
	return $enrolled;
}


function get_unit_data($idnum) {
	global $DB;
	$record = $DB->get_record('course', array('idnumber' => $idnum), $fields='*', $strictness=IGNORE_MISSING);
	return $record;
}


function get_framework_name($course_idnum) {
	global $DB;
	$name = $DB->get_field('enrol_collegedb_teachunits', 'unitdescription', array('unitid' => $course_idnum), $strictness=IGNORE_MISSING);
	return $name;
}


function get_framework_shortname($frameworkname) {
	global $DB;
	$shortname = $DB->get_field('course', 'shortname', array('fullname' => $frameworkname));
	return $shortname;
}


function get_natural_framework($unitshortname) {
	global $DB;
	$fw_name = $DB->get_field('enrol_collegedb_teachunits', 'unitdescription', array('unitshortname' => $unitshortname), $strictness=IGNORE_MULTIPLE);
	$fw_course = $DB->get_record('course', array('fullname'=>$fw_name));
	return $fw_course;
}


function get_all_frameworks_of_metalinked_units_when_parent($child_units) {
	global $DB, $CFG, $COURSE;
	$this_unit = $COURSE->id;
    $courselink_base = $CFG->wwwroot.'/course/view.php?id=';
 //    $query = "courseid = ? AND enrol = ?";
	// $params = array($courseid, 'meta');
	// $sort = 'sortorder ASC';
 //    $child_units = $DB->get_records_select('enrol', $query, $params, $sort, 'customint1', IGNORE_MISSING);

    $unitids = array();
    foreach ($child_units as $c) {
    	if (in_array($c->customint1, $unitids)) {
    		continue;
    	} else {
    		$unitids[] .= $c->customint1;
    	}
    }
    array_unshift($unitids, $this_unit);
    $result = '';
    if ($unitids) {
	    foreach ($unitids as $unit_id) {
	    	$framework_of_child = get_frameworks_of_metas_for_teachers($unit_id);
	    	$result .= '<a class="dropdown-item" href="'.$courselink_base.$framework_of_child->id.'">'.$framework_of_child->fullname.'</a>';
		}
	}

    return $result;
}


function get_parent($parentid) {
	global $DB;
	$parent = $DB->get_record('course', array('id'=>$parentid));
	return $parent;
}


function get_frameworks_of_metas_for_teachers($unitid) {
	global $DB;
	$idnumber = $DB->get_field('course', 'idnumber', array('id'=>$unitid));
	$summary = $DB->get_field('enrol_collegedb_teachunits', 'unitdescription', array('unitid'=>$idnumber), $STRICTNESS=IGNORE_MULTIPLE);
	$fw_shortname = substr($summary, -15);
	$fw_course = $DB->get_record('course', array('shortname'=>$fw_shortname));
	return $fw_course;
}


function get_framework_course_of_parent($parentid) {
	global $DB, $USER;
	$sql = "SELECT c.idnumber
			FROM {course} c
			LEFT JOIN {enrol} e
			ON e.customint1 = c.id
			LEFT JOIN {role_assignments} ra
			ON ra.itemid = e.id
			WHERE e.enrol = 'meta'
			AND ra.userid = '{$USER->id}'
			AND e.courseid = '{$parentid}'";
	$childid = $DB->get_record_sql($sql, array(), $strictness=IGNORE_MISSING);
    if ($childid) {
	    $meta_parent_framework_name = $DB->get_field('enrol_collegedb_teachunits', 'unitdescription', array('unitid' => $childid->idnumber), $strictness=IGNORE_MULTIPLE);
		$meta_parent_framework = $DB->get_record('course', array('fullname' => $meta_parent_framework_name), $fields='*', $strictness=IGNORE_MISSING);
		return $meta_parent_framework;
	}
}


function get_framework_course_of_child($courseid) {
	global $DB, $USER;
	$sql = "SELECT *
			FROM {course} c
			LEFT JOIN {enrol} e
			ON e.courseid = c.id
			LEFT JOIN {role_assignments} ra
			ON ra.itemid = e.id
			WHERE e.enrol = 'meta'
			AND ra.userid = '{$USER->id}'
			AND e.customint1 = '{$courseid}'";
	$meta_child_framework = $DB->get_record_sql($sql, array(), $strictness=IGNORE_MISSING);
    // if ($parent) {
	    // $meta_child_framework_name = $DB->get_field('enrol_collegedb_teachunits', 'unitdescription', array('unitid' => $childid->idnumber), $strictness=IGNORE_MISSING);
		// $meta_child_framework = $DB->get_record('course', array('fullname' => $meta_parent_framework_name), $fields='*', $strictness=IGNORE_MISSING);
		return $meta_child_framework;
	// }
}


function get_parent_id($idnum) {
	global $DB;
	$meta_child_cid = $DB->get_field('course', 'id', array('idnumber' => $idnum), $strictness=IGNORE_MISSING);
	$meta_parent_cid = $DB->get_field('enrol', 'courseid', array('customint1' => $meta_child_cid, 'enrol' => 'meta'), $strictness=IGNORE_MULTIPLE);
	$meta_parent_unitid = $meta_child_cid = $DB->get_field('course', 'idnumber', array('id' => $meta_parent_cid), $strictness=IGNORE_MISSING);
	return $meta_parent_unitid;
}

function get_available_framework_units($parent) {
    global $DB, $CFG, $COURSE;
    $courselink_base = $CFG->wwwroot.'/course/view.php?id=';
    $query = "unitdescription = ?";
	$params = array(strip_tags($parent));
    $list = $DB->get_records_select('enrol_collegedb_teachunits', $query, $params);
    // if ($CFG->debugdisplay && (is_siteadmin())) {
    // 	print_object($list);
    // }
	$result = '';
	$unit_array = array(); // use this to check if unit already processed to avoid duplication
    foreach ($list as $li) {
    	$has_parent = get_parent_id($li->unitid);
	    if ($has_parent) {
	    	$li->unitid = $has_parent;
	    }
	    $unit = get_unit_data($li->unitid);
	    if (($unit) && (!in_array($li->unitid, $unit_array))) {
	    	$user_cat_access = get_user_categories_role($unit->id);
	    	if ($user_cat_access) {
	    		$user_cat_access = 1;
	    	} 
	    	$user_course_access = get_user_access($unit->id);
	    	if ($user_course_access) {
	    		$user_course_access = 1;
	    	}
	    	$user_sys_access = check_system_roles();
	    	if ($user_sys_access) {
	    		$user_sys_access = 1;
	    	}
	    	if ($unit->visible === '1' && ($user_course_access || $user_cat_access || $user_sys_access || is_siteadmin()) || ($unit->visible === '0' && ($user_cat_access || $user_sys_access || is_siteadmin()))) {
		    	$result .= '<a class="dropdown-item" href="'.$courselink_base.$unit->id.'">'.$unit->fullname.'</a>';
			    // if ($CFG->debugdisplay && (is_siteadmin())) {
			    // 	$result .= 'cat='.$user_cat_access.'/course='.$user_course_access.'/sys='.$user_sys_access;
			    // }
			    
			} else {
				continue;
			} 
			$unit_array[] = $li->unitid;
		} else {
			continue;
		}
	}
    return $result; 
}


function get_site_enrolment_methods() {
	global $DB;
	$methods = $DB->get_fieldset_sql("SELECT DISTINCT enrol FROM {enrol}");
	$result = array();
	foreach($methods as $method) {
		$result[$method] = $method;
	}
	return $result;
}


function is_curriculum_course($courseid) {
	global $DB, $CFG;
	if ($DB->record_exists('enrol', array('enrol'=>get_config('theme_remui_child', 'curriculumidentifier'), 'courseid'=>$courseid))) {
		return true;
	} else {
		return false;
	}
}
