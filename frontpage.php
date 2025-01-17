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
 * @package   theme_remui
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $OUTPUT, $USER, $CFG, $PAGE, $SITE;
require_once('common.php');

$systemcontext = context_system::instance();
$templatecontext['contextid'] = $systemcontext->id;
$templatecontext['homepage'] = true;

if (class_exists('local_remuihomepage_plugin')) {
    $homepage = new local_remuihomepage_plugin();
} else {
    $homepage = false;
}

$templatecontext['customhomepage'] = $homepage != false && \theme_remui\toolbox::get_setting('frontpagechooser') == 1;

if ($templatecontext['customhomepage']) {
    $templatecontext['remui_lite'] = true;
    $templatecontext = $homepage->layout($templatecontext);
} else {
    // This Block will add "has-slider and animate-header class to body tag.
    // Will add only when first section is slider otherwiser won't add it.

    $templatecontext['bodyattributes'] = str_replace("class=\"", "class=\"old-frontpage ", $templatecontext['bodyattributes']);
    // Frontpage context.
    // Slider.
    $templatecontext['slider'] = \theme_remui\sitehomehandler::get_slider_data();

    // Marketing spots data.
    $enablesectionbutton = \theme_remui\toolbox::get_setting('enablesectionbutton');

    $displayaboutus = \theme_remui\toolbox::get_setting('frontpageblockdisplay');

    $dispaboutusdiv = 'col-12 col-md-4 mt-md-50';
    $dispmarketingspots   = 'col-12 col-md-7 offset-md-1';
    $dispmarketingspotsin = 'col-12 col-sm-6 spot';

    if (1 == $displayaboutus) {
        $templatecontext['aboutus_hide'] = 'd-none';
    }

    if (2 == $displayaboutus) {
        $dispaboutusdiv = 'col-12 col-md-12 mt-md-50 mb-30 text-center';
        $dispmarketingspots = 'col-12 col-md-12';
        $dispmarketingspotsin = 'col-12 col-sm-3 spot';
    }

    $templatecontext['dispAboutUsDiv'] = $dispaboutusdiv;
    $templatecontext['dispmarketingspots']   = $dispmarketingspots;
    $templatecontext['dispmarketingspotsin'] = $dispmarketingspotsin;


    $templatecontext['marketing_heading'] = format_text(\theme_remui\toolbox::get_setting('frontpageblockheading'));
    $templatecontext['marketing_desc'] = format_text(\theme_remui\toolbox::get_setting('frontpageblockdesc'));
    $templatecontext['marketing_spots'] = array(
        array('heading' => format_text(\theme_remui\toolbox::get_setting('frontpageblocksection1')),
                'description' => format_text(\theme_remui\toolbox::get_setting('frontpageblockdescriptionsection1')),
                'icon' => \theme_remui\toolbox::get_setting('frontpageblockiconsection1'),
                'image' => \theme_remui\toolbox::setting_file_url('frontpageblockimage1', 'frontpageblockimage1')
                ),
        array('heading' => format_text(\theme_remui\toolbox::get_setting('frontpageblocksection2')),
                'description' => format_text(\theme_remui\toolbox::get_setting('frontpageblockdescriptionsection2')),
                'icon' => \theme_remui\toolbox::get_setting('frontpageblockiconsection2'),
                'image' => \theme_remui\toolbox::setting_file_url('frontpageblockimage2', 'frontpageblockimage2')
                ),
        array('heading' => format_text(\theme_remui\toolbox::get_setting('frontpageblocksection3')),
                'description' => format_text(\theme_remui\toolbox::get_setting('frontpageblockdescriptionsection3')),
                'icon' => \theme_remui\toolbox::get_setting('frontpageblockiconsection3'),
                'image' => \theme_remui\toolbox::setting_file_url('frontpageblockimage3', 'frontpageblockimage3')
                ),
        array('heading' => format_text(\theme_remui\toolbox::get_setting('frontpageblocksection4')),
                'description' => format_text(\theme_remui\toolbox::get_setting('frontpageblockdescriptionsection4')),
                'icon' => \theme_remui\toolbox::get_setting('frontpageblockiconsection4'),
                'image' => \theme_remui\toolbox::setting_file_url('frontpageblockimage4', 'frontpageblockimage4')
                )
    );
    // Only if buttons are enabled.
    if ($enablesectionbutton) {
        foreach ($templatecontext['marketing_spots'] as $key => $spot) {
            $spot['button'] = \theme_remui\toolbox::get_setting('sectionbuttontext'.($key + 1));
            $spot['link']   = \theme_remui\toolbox::get_setting('sectionbuttonlink'.($key + 1));
            $templatecontext['marketing_spots'][$key] = $spot;
        }
    }

    // Testimonial section data.
    $templatecontext['testimoniallist'] = \theme_remui\sitehomehandler::get_testimonial_data();

    // Blogs data.
    $hasblogs = false;
    $recentblogs = \theme_remui_child\utility::get_recent_blogs(0, 3);
    if (!empty($CFG->enableblogs) && is_array($recentblogs) && !empty($recentblogs)) {
        $hasblogs = true;
    }
    $templatecontext['blog'] = [
        'hasblogs' => $hasblogs,
        'blogs' => array_values($recentblogs),
    ];
}

echo $OUTPUT->render_from_template('theme_remui_child/frontpage', $templatecontext);
