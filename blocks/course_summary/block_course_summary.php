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
 * Course summary block
 *
 * @package    block_course_summary
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot. '/course/lib.php');


class block_course_summary extends block_base {

    /**
     * @var bool Flag to indicate whether the header should be hidden or not.
     */
    private $headerhidden = true;

    function init() {
        $this->title = get_string('pluginname', 'block_course_summary');
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    function specialization() {
        // Page type starts with 'course-view' and the page's course ID is not equal to the site ID.
        if (strpos($this->page->pagetype, PAGE_COURSE_VIEW) === 0 && $this->page->course->id != SITEID) {
            $this->title = get_string('coursesummary', 'block_course_summary');
            $this->headerhidden = false;
        }
    }

    function get_content() {
        global $CFG, $OUTPUT, $PAGE;

        require_once($CFG->libdir . '/filelib.php');

        if($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return '';
        }

        $this->content = new stdClass();
        $options = new stdClass();
        $options->noclean = true;    // Don't clean Javascripts etc
        $options->overflowdiv = true;
        $context = context_course::instance($this->page->course->id);
        
        $this->page->course->summary = file_rewrite_pluginfile_urls($this->page->course->summary, 'pluginfile.php', $context->id, 'course', 'summary', NULL);
        // $this->content->text = format_text($this->page->course->summary, $this->page->course->summaryformat, $options);
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $res=$courserenderer->course_fromdb_by_id_self($this->page->course->id);
        
        $content = '<div>';
        
        // 开课时间
        $content .= '<div class="row" style="color:#999999;">开课时间: '. $res['startdate'];
        $content .= '</div>';

        // 结束课程时间
        $content .= '<div class="row" style="color:#999999;">结课时间: '. $res['enddate'];
        $content .= '</div>';

        // 授课教师
        $content .= '<div class="row" style="color:#999999">授课老师：';
        foreach($res['teachers'] as $item){
            $content .= '<div>';
            $content .= $item['imgurl'];
            $teacher_url = new moodle_url('/user/view.php?id='. $item["id"] .'&course=1');
            $content .= '<a href="'.$teacher_url.'"><span style="margin-left:1rem;">'.$item['name'].'</span></a>';
            $content .= '</div>';
        }
        $content .= '</div>';

        // 简介
        $content .= '<div class="row" style="color:#999999;margin-top:1rem;font-size:0.9em;">课程简介：'.$this->page->course->summary;
        $content .= '</div>';


        $content .= '</div>';

        $this->content->text = $content;
        $this->content->footer = '';

        return $this->content;
    }

    function hide_header() {
        return $this->headerhidden;
    }

}


