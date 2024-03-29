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
 * Log creation script for the quiz module.
 *
 * @package    logstore_xapi
 * @copyright (C) 2022 Yamaguchi University (gh-cc@mlex.cc.yamaguchi-u.ac.jp)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_quiz\question_answered;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

/**
 * This function creates answer log about numerical question type.
 * @param array $config - array of configurations.
 * @param object $event - object of Moodle event.
 * @param object $questionattempt - object of question attempt.
 * @param object $question - object of question.
 * @return string - xAPI formatted log statement.
 */
function numerical(array $config, \stdClass $event, \stdClass $questionattempt, \stdClass $question) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $lang = utils\get_course_lang($course);

    $responsesummary = '';

    if (!empty($questionattempt->responsesummary)) {
        $responsesummary = $questionattempt->responsesummary;
    }

    if (!empty($responsesummary)) {
        $responsesummary = utils\get_string_html_removed(trim($responsesummary));
        $responsesummary = utils\get_string_math_removed(trim($responsesummary));
    }

    if (empty($questionattempt->rightanswer)) {
        $questionattempt->rightanswer = '';
    }

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/answered',
            'display' => [
                $lang => 'answered'
            ],
        ],
        'object' => [
            'id' => utils\get_quiz_question_id($config, $coursemodule->id, $question->id),
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'name' => [
                    $lang => utils\get_string_html_removed($question->questiontext)
                ],
                'interactionType' => 'numeric',
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => $responsesummary,
            'completion' => $questionattempt->responsesummary !== '',
            'success' => $questionattempt->rightanswer === $questionattempt->responsesummary,
            'extensions' => [
                'http://learninglocker.net/xapi/cmi/numeric/response' => floatval($questionattempt->responsesummary),
            ],
        ],
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'grouping' => [
                    utils\get_activity\site($config),
                    utils\get_activity\course($config, $course),
                    utils\get_activity\course_quiz($config, $course, $event->contextinstanceid),
                    utils\get_activity\quiz_attempt($config, $attempt->id, $coursemodule->id),
                ],
                'category' => [
                    utils\get_activity\source($config),
                ]
            ],
        ]
    ]];
}
