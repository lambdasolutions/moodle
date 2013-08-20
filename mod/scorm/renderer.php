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
 * Defines the renderer for the quiz module.
 *
 * @package    mod
 * @subpackage scorm
 * @copyright  2013 Dan Marsden
 * @author     Dan Marsden <dan@danmarsden.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The renderer for the scorm module.
 *
 * @copyright  2013 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_scorm_renderer extends plugin_renderer_base {
    public function view_user_heading($user, $course, $baseurl, $attempt, $maxattempt) {
        $output = '';
        $output .= $this->box_start('generalbox boxaligncenter');
        $output .= html_writer::start_tag('div', array('class' => 'mdl-align'));
        $output .= $this->user_picture($user, array('courseid'=>$course->id, 'link' => true));
        $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
        $output .= html_writer::link($url, fullname($user));
        $pb = new paging_bar($maxattempt, $attempt, 1, $baseurl, 'pattempt');
        $output .=  $this->render($pb);
        $output .= html_writer::end_tag('div');
        $output .= $this->box_end();
        return $output;
    }
    /**
     * override paging bar renderer to use custom label - attempt.
     *
     * @param paging_bar $pagingbar
     * @return string
     */
    protected function render_paging_bar(paging_bar $pagingbar) {
        $output = '';
        $pagingbar = clone($pagingbar);
        $pagingbar->prepare($this, $this->page, $this->target);

        if ($pagingbar->totalcount > $pagingbar->perpage) {
            $output .= get_string('attempt', 'scorm') . ':';

            if (!empty($pagingbar->previouslink)) {
                $output .= '&#160;(' . $pagingbar->previouslink . ')&#160;';
            }

            if (!empty($pagingbar->firstlink)) {
                $output .= '&#160;' . $pagingbar->firstlink . '&#160;...';
            }

            foreach ($pagingbar->pagelinks as $link) {
                $output .= "&#160;&#160;$link";
            }

            if (!empty($pagingbar->lastlink)) {
                $output .= '&#160;...' . $pagingbar->lastlink . '&#160;';
            }

            if (!empty($pagingbar->nextlink)) {
                $output .= '&#160;&#160;(' . $pagingbar->nextlink . ')';
            }
        }

        return html_writer::tag('div', $output, array('class' => 'paging'));
    }

}