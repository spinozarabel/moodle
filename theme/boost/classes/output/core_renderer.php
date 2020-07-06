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

// added for using filtering of theme
use filter_manager;

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
	
	protected function render_custom_menu(custom_menu $menu) {
        // Our code will go here shortly
		$mycourses = $this->page->navigation->get('mycourses');
 
		if (isloggedin() && $mycourses && $mycourses->has_children()) {
			$branchlabel = get_string('mycourses');
			$branchurl   = new moodle_url('/course/index.php');
			$branchtitle = $branchlabel;
			$branchsort  = 10000;
		 
			$branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
		 
			foreach ($mycourses->children as $coursenode) {
				$branch->add($coursenode->get_content(), $coursenode->action, $coursenode->get_title());
			}
		}
 
		return parent::render_custom_menu($menu);
    }

}
