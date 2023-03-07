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

namespace theme_remui_child\output;

use moodle_url;
use moodle_page;
use html_writer;
use pix_icon;
use context_course;
use core_text;
use stdClass;
use action_menu;
use context_system;
use core_plugin_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/theme/remui_child/lib.php');
// require_once($CFG->dirroot.'/blocks/teamsrequest/addateam.php');
/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_remui
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_remui\output\core_renderer {

    protected $themeconfig;
     
    /**
     * Return HTML for site announcement.
     *
     * @return string Site announcement HTML
     */
    public function render_additional_site_announcements() {
    	global $DB, $CFG, $USER;
    	$sql = "SELECT ud.data 
			FROM {user_info_data} ud 
			JOIN {user_info_field} uf ON uf.id = ud.fieldid
			WHERE ud.userid = :userid AND uf.shortname = :fieldname";
		$params = array('userid' =>  $USER->id, 'fieldname' => 'usertype');
		$usertype = $DB->get_field_sql($sql, $params);
        $enableannouncements = get_config('theme_remui_child','enableannouncements');
        $announcements = '';
        $announcementcount = get_config('theme_remui_child','announcementcount');
        if ($enableannouncements) {
        	foreach(range(1,$announcementcount+1) as $n) {
        		$targetaudience = get_config('theme_remui_child','targetaudience'.$n);
    			if ($targetaudience === $usertype || $targetaudience === 'all' || empty($targetaudience) || is_siteadmin() ) {
		            $type = get_config('theme_remui_child','announcementtype'.$n);
		            $message = get_config('theme_remui_child','announcementtext'.$n);
		            $announcements .= "<div class='alert alert-{$type} dark text-center rounded-0 site-announcement m-b-0'>";
		            if (!$targetaudience) {
		            	$attr = "title='Notice to All Users - ".strip_tags($message)."'";
		            }
		            if ($targetaudience && is_siteadmin()) {
		            	$attr = "title='Notice to ".$targetaudience." - ".strip_tags($message)."'";
		            }
		            $announcements .= "<div class='announcement_inner' ".$attr.">".$message."</div>";
		            $announcements .= "</div>";
		        }
        	}
        }
        return $announcements;
    }

    /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;
        $context = $form->export_for_template($this);

        $customizer = \theme_remui\customizer\customizer::instance();

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true,
            ['context' => \context_course::instance(SITEID), "escape" => false]);
        $context->siteicon = \theme_remui\toolbox::get_setting('siteicon');
        $context->loginpage_context = $this->should_display_logo();

        $customlogo = $customizer->get_config('login-panel-logo');
        if ($customlogo != '') {
            $context->loginpage_context['logourl'] = $customlogo->out(false);
            $context->loginpage_context['logominiurl'] = $customlogo->out(false);
        }
        
        $context->loginsocial_context = \theme_remui\utility::get_footer_data(1);
        $context->logopos = get_config('theme_remui', 'brandlogopos');
        $sitetext = get_config('theme_remui', 'brandlogotext');
        if ($sitetext != '') {
            $context->sitedesc = $sitetext;
        }

        if (get_config('theme_remui_child', 'prioritise_oidc') == 1) {
        	return $this->render_from_template('theme_remui_child/loginform_oidc', $context);
        } else {
        	return $this->render_from_template('core/loginform', $context);

        }
    }

     /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header
     */
    public function full_header() {
        global $PAGE, $COURSE, $CFG, $DB, $USER;

        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'row'));
        $html .= html_writer::start_tag('div', array('class' => 'col-12'));
        $html .= html_writer::start_tag('div', array('class' => ''));
        $html .= html_writer::start_tag('div', array('class' => 'card-body d-flex justify-content-between flex-wrap'));
        $html .= $this->context_header();
        $html .= html_writer::end_tag('div');
        require_once($CFG->dirroot.'/blog/lib.php');

        $context = context_course::instance($COURSE->id);
        /** If enabled, render the course framework header navigation **/

        // $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::start_tag('div', array('class' => 'col-sm-12 col-md-8 col-lg-8 course-info'));
        if (is_curriculum_course($COURSE->id) === true) {
	        
	        $enableframeworks = get_config('theme_remui_child', 'enableframeworks');
	        $coursesummary = strip_tags($COURSE->summary);
	        $framework_check_in_summary = substr($coursesummary, -15);
	        if ( (!preg_match("#[a-zA-Z0-9]{10}+-[a-zA-Z]{1}+[0-9]{1}+[a-zA-Z]{2}#",$framework_check_in_summary)) & (strpos($coursesummary, 'Child of') === FALSE) & ($coursesummary !== 'Multiple Groups') ) {
	            $html .= html_writer::tag('div', '<strong>Course Summary:</strong> '.$coursesummary, array('class' => 'coursesummary'));
	        }

	        if ($enableframeworks === '1') {  
	            if ($PAGE->pagelayout == 'course') { // is it a course page 
	                if (has_capability('moodle/course:manageactivities', $context)) { // user has teacher or higher permissions
	                    if (strpos($COURSE->shortname, '/') !== false) { // is it a unit course?
	                        $fw = get_natural_framework($COURSE->shortname);
	                        $child_units = $DB->get_records_sql('SELECT customint1 FROM {enrol} WHERE courseid = ? AND enrol = "meta"', array($COURSE->id));
	                        $parent_unit = $DB->get_record('enrol', array('customint1'=>$COURSE->id));
	                        if ($child_units) { // does it have children?
	                            $ischild = 0;
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group'));
	                            $html .= html_writer::link($CFG->wwwroot.'/user/index.php?id='.$COURSE->id, '<i class="fa fa-sitemap"></i> Multiple Groups', array('id' => 'course_summary_link', 'class' => 'btn btn-primary', 'title' => 'View Enrolments'));
	                            $html .= html_writer::tag('button', '', array('class' => 'btn btn-outline-primary dropdown-toggle dropdown-toggle-split','data-toggle' => 'dropdown', 'title' => 'All Groups'));
	                            $html .= html_writer::start_tag('div', array('class' => 'dropdown-menu dropdown-menu dropdown-menu-left dropdown-menu-media p-0'));
	                            $html .= html_writer::tag('div', 'All Groups', array('class' => 'dropdown-header'));
	                            $html .= html_writer::tag('div', '', array('class' => 'dropdown-divider'));
	                            $html .= get_all_frameworks_of_metalinked_units_when_parent($child_units);
	                            $html .= html_writer::end_tag('div');
	                            $html .= html_writer::end_tag('div');
	                        } elseif ($parent_unit) { // does it have a parent?
	                            $ischild = 1;
	                            $parent = get_parent($parent_unit->courseid);
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group', 'role' => 'group'));
	                            $html .= html_writer::link($CFG->wwwroot.'/course/view.php?id='.$fw->id, '<i class="fa fa-sitemap"></i> '.$fw->fullname, array('id' => 'course_summary_link', 'class' => 'btn btn-primary', 'title' => 'Framework'));
	                            $html .= html_writer::link($CFG->wwwroot.'/course/view.php?id='.$parent->id, '<i class="fa fa-users"></i> Child of '.$parent->shortname, array('id' => 'course_summary_link', 'class' => 'btn btn-outline-primary', 'title' => $parent->shortname));
	                            $html .= html_writer::end_tag('div');
	                        } else { // standalone with no meta links
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group', 'role' => 'group'));
	                            $html .= html_writer::link($CFG->wwwroot.'/course/view.php?id='.$fw->id, '<i class="fa fa-sitemap"></i> '.$fw->fullname, array('id' => 'course_summary_link', 'class' => 'btn btn-primary', 'title' => 'Framework'));
	                            $html .= html_writer::end_tag('div');
	                            }
	                    } else { // its not a unit course page
	                        $ischild = 0;
	                        if (preg_match("#[a-zA-Z0-9]{10}+-[a-zA-Z]{1}+[0-9]{1}+[a-zA-Z]{2}#",$framework_check_in_summary)) {
	                                $coursesummary = strip_tags($COURSE->summary);
	                            } else {
	                                $coursesummary = $COURSE->fullname;
	                            }
	                        $framework_units = get_available_framework_units($COURSE->fullname);
	                        if ($framework_units) { // does this framework have standalone units Moodle?
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group'));
	                            $html .= html_writer::tag('div', '<i class="fa fa-sitemap"></i> '.$coursesummary, array('id' => 'course_summary', 'class' => 'btn btn-primary', 'title' => 'Framework'));
	                            $html .= html_writer::tag('button', '', array('class' => 'btn btn-outline-primary dropdown-toggle dropdown-toggle-split','data-toggle' => 'dropdown', 'title' => 'Standalone Unit Links'));
	                            $html .= html_writer::start_tag('div', array('class' => 'dropdown-menu dropdown-menu-left dropdown-menu-media p-0'));
	                            $html .= html_writer::tag('div', 'Standalone Unit Pages', array('class' => 'dropdown-header'));
	                            $html .= html_writer::tag('div', '', array('class' => 'dropdown-divider'));
	                            $html .= $framework_units;
	                            $html .= html_writer::end_tag('div');
	                            $html .= html_writer::end_tag('div');
	                        } else {
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group'));
	                            $html .= html_writer::tag('div', '<i class="fa fa-sitemap"></i> '.$coursesummary, array('id' => 'course_summary', 'class' => 'btn btn-primary', 'title' => 'Framework'));
	                            $html .= html_writer::end_tag('div');
	                        }
	                    }
	                    if ($COURSE->visible == '0' && $ischild != 1) {
	                        $html .= html_writer::link($CFG->wwwroot.'/course/edit.php?id='.$COURSE->id, '<i class="fa fa-cog"></i> This page is currently hidden to students', array('id' => 'course_hidden', 'class' => 'btn btn-outline-danger', 'title' => 'Edit settings'));
	                    } if ($COURSE->visible == '0' && $ischild == 1) {
	                        $html .= html_writer::tag('span', '<i class="fa fa-eye-slash"></i> Child course: hidden', array('id' => 'course_hidden', 'class' => 'btn btn-danger', 'title' => 'Child courses should always remain hidden'));
	                    } 
	                } else { // user is guest or student
	                    if (strpos($COURSE->shortname, '/') !== false) { // is it a unit page?
	                        $pframework = get_framework_course_of_parent($COURSE->id);
	                        if ($pframework) { // can we find the framework course page as a parent?
	                            if ($pframework->visible === '0') { //framework page is hidden
	                                $html .= html_writer::tag('span', '<i class="fa fa-sitemap"></i> '.$pframework->fullname, array('id' => 'course_summary', 'class' => 'btn btn-primary'));
	                            } else { // framework page is visible
	                                $html .= html_writer::link($CFG->wwwroot.'/course/view.php?id='.$pframework->id, '<i class="fa fa-sitemap"></i> '.$pframework->fullname, array('id' => 'course_summary_link', 'class' => 'btn btn-outline-primary', 'title' => 'Go to main course page'));
	                            }
	                        } else { // can't find a parent framework - must a child or not be linked
	                            $natural_fw = get_natural_framework($COURSE->shortname);
	                            $html .= html_writer::link($CFG->wwwroot.'/course/view.php?id='.$natural_fw->id, '<i class="fa fa-sitemap"></i> '.$natural_fw->fullname, array('id' => 'course_summary_link', 'class' => 'btn btn-outline-primary', 'title' => 'Go to main course page'));
	                        }
	                    } else { // not a unit page
	                        $framework_units = get_available_framework_units($COURSE->fullname);
	                        if ($framework_units) { // does this framework have standalone units Moodle?
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group'));
	                            $html .= html_writer::tag('div', '<i class="fa fa-sitemap"></i> '.$coursesummary, array('id' => 'course_summary', 'class' => 'btn btn-primary', 'title' => 'Framework'));
	                            $html .= html_writer::tag('button', '', array('class' => 'btn btn-outline-primary dropdown-toggle dropdown-toggle-split','data-toggle' => 'dropdown', 'title' => 'Standalone Unit Links'));
	                            $html .= html_writer::start_tag('div', array('class' => 'dropdown-menu'));
	                            $html .= html_writer::tag('div', 'Standalone Unit Pages', array('class' => 'dropdown-header'));
	                            $html .= html_writer::tag('div', '', array('class' => 'dropdown-divider'));
	                            $html .= $framework_units;
	                            $html .= html_writer::end_tag('div');
	                            $html .= html_writer::end_tag('div');
	                        } else { // no standalone units
	                            $html .= html_writer::start_tag('div', array('id' => 'course_summary', 'class' => 'btn-group'));
	                            $html .= html_writer::tag('div', '<i class="fa fa-sitemap"></i> '.$coursesummary, array('id' => 'course_summary', 'class' => 'btn btn-primary', 'title' => 'Framework'));
	                            $html .= html_writer::end_tag('div');
	                        }
	                    }
	                }
	            }  	
	        }
    	}
		$html .= html_writer::end_tag('div'); 


    	// Teams section
    	if ($PAGE->pagelayout == 'course') { // is it a course page 
	    	$enableteams = get_config('theme_remui_child', 'enableteams');
	    	$isconnected = \local_o365\utils::is_o365_connected($USER->id) === true;
	    	if ($isconnected) {
		    	if ($enableteams === '1') {
		    		require_once($CFG->dirroot.'/blocks/teamsrequest/addateam.php');
		    		require_once($CFG->dirroot.'/local/o365/classes/rest/unified.php');
		    		require_once($CFG->dirroot.'/auth/oidc/lib.php');
					require_once($CFG->dirroot.'/local/o365/lib.php');
		    		// Is user connected.
		    		
					$html .= '<div class="header_teams_section col-12" style="text-align: right;">';
			    	if (teams_check($COURSE->id) === true) {
			    		
				    		$groupobjectID = $DB->get_field('local_o365_objects', 'objectid', array('moodleid' => $COURSE->id, 'subtype' => 'courseteam'));
				    		$channelID = $DB->get_field('local_o365_objects', 'objectid', array('moodleid' => $COURSE->id, 'subtype' => 'courseteamchannel'));
				    		$tenantID = get_config('local_o365', 'aadtenantid');
				    		$encodedID = urlencode( $channelID );
				    		$html .= '<a target="_blank" class="btn btn-secondary header_teams_link" href="https://teams.microsoft.com/l/team/'.$encodedID.'/conversations?groupId='.$groupobjectID.'&tenantId='.$tenantID.'" title="'.substr($COURSE->fullname, 0, -16).' Team" style="text-align: center;"><span>Course Team</span></a>';
			    		
				    } else {
				    	if (strpos($COURSE->shortname, '-') == true) {
					    	if ($isconnected) {
								$roles = get_user_roles($context, $USER->id, true);
								$role = key($roles);
								$rolename = $roles[$role]->shortname;
								if($rolename == "editingteacher"){
					    			require_once($CFG->dirroot.'/blocks/teamsrequest/lib.php');
					    			if (teamrequested($COURSE->id)) {
							    		$html .= '<div class="header_teams_link" disabled="disabled"><i class="fa fa-check" style="color:green"></i> Team requested... awaiting creation <i id="teamsmoreinfo" class="fa fa-question-circle"></i>';
							    	} else {
						    			$html .= '<form action="" method="POST" id="reqateam">';
										$html .= '<input type="hidden" id="addval" value="'.$COURSE->id.'">';
										$html .= '<input type="hidden" id="userid" value="'.$USER->id.'">';
						    			$html .= '<input type="hidden" id="posturl" value="'.$_SERVER['REQUEST_URI'].'">';
										$html .= '<button id="submit" type="submit" class="btn btn-secondary header_teams_link" id="reqateam" style="text-align: left;text-align: left; box-shadow: 0px 1px 2px 0px #9e75d4;"><span style="font-weight:600;">There is no Team associated with this course yet. Click here to enable a Team for this course.</span></button>';
										$html .= '</form>';
									}
					    			$html .= '<div id="teamcreated" class="col-8" style="text-align: left;">'.get_string('team_requested','theme_remui_child');
					    		}
					    	} else {
								$html .= '<div class="connectionstatus alert alert-info"><h5>'.get_string('not365connected','theme_remui_child').' <a class="warning" href="'.$CFG->wwwroot.'/local/o365/ucp.php?action=connection">Connect Now</a></h5></div>';
							}
				    	}
				    }
					$html .= '</div>';
				}
			} else {
				$html .= '<div class="connectionstatus alert alert-info"><h5>'.get_string('sitenot365connected','theme_remui_child').' <a class="warning" href="'.$CFG->wwwroot.'/local/o365/ucp.php?action=connection">Connect Now</a></h5></div>';
			}
		}

    	$html .= html_writer::end_tag('div');
    	$html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('header');
        return $html;
    }
}