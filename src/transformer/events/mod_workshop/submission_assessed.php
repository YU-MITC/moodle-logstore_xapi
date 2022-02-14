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
 * Log creation script for the workshop module.
 *
 * @package    logstore_xapi
 * @copyright (C) 2022 Yamaguchi University (gh-cc@mlex.cc.yamaguchi-u.ac.jp)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_workshop;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

/**
 * This function creates assessed log about workshop module.
 * @param array $config - array of configurations.
 * @param object $event - object of Moodle event.
 * @return string - xAPI formatted log statement.
 */
function submission_assessed(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $assessment = $repo->read_record_by_id('workshop_assessments', $event->objectid);
    $grades = $repo->read_records('workshop_grades', ['assessmentid' => $assessment->id]);
    $submission = $repo->read_record_by_id('workshop_submissions', $assessment->submissionid);
    $workshop = $repo->read_record_by_id('workshop', $submission->workshopid);
    $reviewer = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $target = $repo->read_record_by_id('user', $event->relateduserid);
    $lang = utils\get_course_lang($course);

    $gradestatement = '{title: "' . $submission->title . '"} {dimensions: ';
    $scoreraw = 0;
    foreach($grades as $grade) {
        $comment = '';
        if (!empty($grade->peercomment)) {
            $comment =  utils\get_string_html_removed(trim($grade->peercomment));;
        }
        $gradestatement .= '{dimensionid: ' . $grade->dimensionid . ', grade: ' . $grade->grade;
        $gradestatement .= ', peercomment: "' . $grade->peercomment . '"} ';

        $scoreraw = $scoreraw + $grade->grade;
        
    }

    $gradestatement .= "} ";

    $feedback = '';

    if (!empty($assessment->feedbackauther)) {
        $feedback = utils\get_string_html_removed(trim($assessment->feedbackauther));
    }

    $gradestatement .= '{feedback: "' . $feedback  .'"}';

    $statement = [
        'actor' => utils\get_user($config, $target),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/commented',
            'display' => [
                $lang => 'submission assessed'
            ],
        ],
        'object' => utils\get_activity\course_workshop($config, $event->contextinstanceid, $workshop->name, $lang),
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => $gradestatement
        ],
        'context' => [
            'instructor' => utils\get_user($config, $reviewer),
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'grouping' => [
                    utils\get_activity\site($config),
                    utils\get_activity\course($config, $course)
                ],
                'category' => [
                    utils\get_activity\source($config),
                ],
            ],
        ]
    ];

    return [$statement];
}
