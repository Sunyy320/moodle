<?php
/**
 * Created by PhpStorm.
 * User: yuanyuansun
 * Date: 2018/4/30
 * Time: 下午6:59
 */
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

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';

global $CFG, $USER;
require_once($CFG->libdir. '/coursecatlib.php');
$coursecat = coursecat::get(0);

$courseid = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/user/trance.php', array('id'=>$courseid)));


/// basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');
$PAGE->set_title("学习追踪");
$PAGE->set_context(context_course::instance($courseid));

$context = context_course::instance($course->id);
require_capability('gradereport/user:view', $context);

echo $OUTPUT->header();
echo $OUTPUT->heading('学习追踪');

$allCourses = $coursecat->get_courses_works($course->id);
$studentCourses = $coursecat->get_courses_works_complete_status($course->id);

$data = array();
$allCount = count($allCourses);
echo "<h4>参与课程人数: ".count($studentCourses)."</h4>";
// 进行数据的对比
foreach ($studentCourses as $key=>$s) {
    // 未完成和已完成
    $not = '<ol>';
    $hasCompleted = '<ol>';
    foreach ($allCourses as $a) {
        $flag = 0;
        foreach ($s as $ss){
            if ($a->name == $ss->itemname){
                $hasCompleted .= '<li>'. $a->name.'</li>';
                $flag= 1;
                break;
            }
        }
        if ($flag == 0){
            $not .= '<li>'. $a->name.'</li>';
        }
    }
    $not .= '</ol>';
    $hasCompleted .= '</ol>';
    $data[]= array(
        'name' => $key,
        'per' => count($s).'/'. $allCount,
        'not' => $not,
        'has' => $hasCompleted
    );
}

$table = new html_table();
$table->head = array("姓名", "已完成/全部","未完成", "已完成");
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
