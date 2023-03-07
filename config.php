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
 * Edwiser RemUI Config
 * @package    theme_remui
 * @copyright  (c) 2018 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$THEME->doctype='html5';
$THEME->name='remui_child'; // Name of the theme
$THEME->sheets=array('style'); // Stylesheets for the theme
$THEME->parents=['remui']; // Parent Theme
$THEME->enable_dock=false;
$THEME->yuicssmodules=array();
$THEME->javascripts=array('browser_check','stop_modal_video','scripts'); //JS Files for the theme
$THEME->rendererfactory='theme_overridden_renderer_factory';
$THEME->csspostprocess='theme_remui_process_css';
$THEME->requiredblocks='';
$THEME->addblockposition=BLOCK_ADDBLOCK_POSITION_FLATNAV;
