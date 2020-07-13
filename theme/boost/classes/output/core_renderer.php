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

namespace theme_boost\output;

use moodle_url;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \core_renderer {

    public function edit_button(moodle_url $url) {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }
        $button = new \single_button($url, $editstring, 'post', ['class' => 'btn btn-primary']);
        return $this->render_single_button($button);
    }

// customization added my Madhu from here on

/**
 * over ride function in parent to render custom menu
 *
 * @menu
 * @copyright  Madhu Avasarala
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
	protected function render_custom_menu(\custom_menu $menu)
    {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');
        // mycourses menu on all pages
        if (isloggedin() && !isguestuser() && $mycourses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC'))
        {
            $branchlabel = get_string('mycourses') ;
            $branchurl   = new moodle_url('/course/index.php');
            $branchtitle = $branchlabel;
            $branchsort  = 10000 ; // lower numbers = higher priority e.g. move this item to the left on the Custom Menu
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

            foreach ($mycourses as $mycourse)
            {
                $branch->add($mycourse->shortname, new moodle_url('/course/view.php', array('id' => $mycourse->id)), $mycourse->fullname);
            }
        }
        // admin menu
        if (isloggedin() && is_siteadmin())
        {
            $branchlabel = "Admin";
            $branchurl   = new moodle_url('/admin/search.php');
            $branchtitle = $branchlabel;
            $branchsort  = 11000 ;
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
            $branch->add('Maintanance', new moodle_url('/admin/settings.php?section=maintenancemode'), 'Maintanance');
            $branch->add('Cohorts', new moodle_url('/cohort/index.php'), 'Cohorts');
            
        }
        // we use the rendering of the parent boost renderer
		return parent::render_custom_menu($menu);
    }

}
