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
 * @copyright  2012 Bas Brands, www.basbrands.nl, Madhu Avasarala
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
        global $CFG, $PAGE, $COURSE, $DB;
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
            // Maintanance: add as menu element
            $branch->add('Maintanance', new moodle_url('/admin/settings.php?section=maintenancemode'), 'Maintanance');
            // Cohorts: add as menu element
            $branch->add('Manage Cohorts', new moodle_url('/cohort/index.php'), 'Manage Cohorts');
            // Manage Activities plugins settings
            $branch->add('Manage Activities', new moodle_url('/admin/modules.php'), 'Manage Activities');
            // Browse list of users
            $branch->add('Browse users', new moodle_url('/admin/user.php'), 'Browse users');
            // Bulk user actions
            $branch->add('Bulk user actions', new moodle_url('/admin/user_bulk.php'), 'Bulk user actions');
            // user profile fields
            $branch->add('User Profile Fields', new moodle_url('/user/profile/index.php'), 'User Profile Fields');
            // Capability Overview
            $branch->add('Capability Overview', new moodle_url('/admin/tool/capability/index.php'), 'Capability Overview');
            //
        }

        $context    = $PAGE->context ?? null;
        // we only want to add the menu if valid context exists
        if ($context)
        {
            // if on a course page
            if (isloggedin()  &&  ($COURSE->id != SITEID))
            {
                // add menu branch called ThisCourse, appears as menu heading
                $course_id      = $COURSE->id;
                $branchlabel    = "ThisCourse";
                $branchurl      = new moodle_url('/course/index.php');
                $branchtitle    = $branchlabel;
                $branchsort     = 12000 ;
                $branch_thiscourse         = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
                // Now start adding menu items under this menu branch
                //
                // Add menu items
                $branch_thiscourse->add('Assignments', new moodle_url('/mod/assign/index.php' . '?id=' . $course_id),  'Assignments');
                $branch_thiscourse->add('Forums',      new moodle_url('/mod/forum/index.php'  . '?id=' . $course_id),   'Forums');
                $branch_thiscourse->add('Quizzes',     new moodle_url('/mod/quiz/index.php'   . '?id=' . $course_id),   'Quizzes');
                $branch_thiscourse->add('H5P',         new moodle_url('/mod/h5pactivity/index.php'   . '?id=' . $course_id),   'H5P');
                //
                // Add new branch menu called Sections
                $branchlabel    = "Topics";
                $branchurl      = new moodle_url('/course/index.php');
                $branchtitle    = $branchlabel;
                $branchsort     = 13000 ;
                $branch_sections = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
                // Now start adding menu items under this menu branch
                $modinfo    = get_fast_modinfo($this->page->course);
                $sections   = $modinfo->get_section_info_all();
                $section_num = 0;
                foreach($sections as $idx => $section)
                {
                    if (!$section->uservisible && !has_capability('moodle/grade:edit', $context))
                    {
                        // section is hiden and user is not a teacher so don't add this section to menu
                        $section_num    += 1; // increment section number, 1st section starts with 0
                        continue;   // skip this iteration and continue with next interation
                    }
                    // add a menu item for each section in the loop
                    $section_name   = get_section_name($COURSE, $section);
                    $section_url    = new moodle_url('/course/view.php' . '?id=' . $course_id . '#section-' . $section_num);
                    $branch_sections->add($section_name, $section_url, $section_name);
                    $section_num    += 1; // increment section number, 1st section starts with 0
                }
            }

            // is teacher in course page? teacher is defined as anyone who has capability to grade in this course
            if (isloggedin()  &&  ($COURSE->id != SITEID) && has_capability('moodle/grade:edit', $context))
            {
                // add menu items specific to teachers only
                // Easy access to participants
                $branch_thiscourse->add('Participants',   new moodle_url('/user/index.php'   . '?id=' . $course_id),   'Participants');
                // Easy access to enrolment methods
                $branch_thiscourse->add('Enrolment Methods',   new moodle_url('/enrol/instances.php'   . '?id=' . $course_id),   'Enrolment Methods');
                // easy access to Activity completion for this course
                $branch_thiscourse->add('Activity Completion', new moodle_url('/report/progress/index.php' . '?course=' . $course_id),   'Activity completion');
                // easy access to Activity report for this course
                $branch_thiscourse->add('Activity report', new moodle_url('/report/outline/index.php' . '?id=' . $course_id),   'Activity report');
                // easy access to Question bank for this course
                $branch_thiscourse->add('Question Bank',       new moodle_url('/question/edit.php'   . '?courseid=' . $course_id),   'Question Bank');
            }
        }
        // we use the rendering of the parent boost renderer
    	return parent::render_custom_menu($menu);
    }

}
