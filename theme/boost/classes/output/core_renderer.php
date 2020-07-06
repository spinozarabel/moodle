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
	
	/**
     * Applies Moodle filters to custom menu and custom user menu.
     *
     * @param string $custommenuitems Current custom menu object.
     * @return Rendered custom_menu that has been filtered.
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG, $PAGE;

        // Don't apply auto-linking filters.
        $filtermanager = filter_manager::instance();
        $filteroptions = array('originalformat' => FORMAT_HTML, 'noclean' => true);
        $skipfilters = array('activitynames', 'data', 'glossary', 'sectionnames', 'bookchapters');

        // Filter custom user menu.
        // Don't filter custom user menu on the settings page. Otherwise it ends up
        // filtering the edit field itself resulting in a loss of the tag.
        if ($PAGE->pagetype != 'admin-setting-themesettings' && stripos($CFG->customusermenuitems, '{') !== false) {
            $CFG->customusermenuitems = $filtermanager->filter_text($CFG->customusermenuitems, $PAGE->context,
                    $filteroptions, $skipfilters);
        }

        // Filter custom menu.
        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        if (stripos($custommenuitems, '{') !== false) {
            $custommenuitems = $filtermanager->filter_text($custommenuitems, $PAGE->context, $filteroptions, $skipfilters);
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }
	
	/**
     * We want to show the custom menus as a list of links in the footer on small screens.
     * Just return the menu object exported so we can render it differently.
     */
    public function custom_menu_flat() {
        global $CFG, $PAGE;
        $custommenuitems = '';

        // Don't apply auto-linking filters.
        $filtermanager = filter_manager::instance();
        $filteroptions = array('originalformat' => FORMAT_HTML, 'noclean' => true);
        $skipfilters = array('activitynames', 'data', 'glossary', 'sectionnames', 'bookchapters');

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        if (stripos($custommenuitems, '{') !== false) {
            $custommenuitems = $filtermanager->filter_text($custommenuitems, $PAGE->context, $filteroptions, $skipfilters);
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $custommenu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        return $custommenu->export_for_template($this);
    }

}
