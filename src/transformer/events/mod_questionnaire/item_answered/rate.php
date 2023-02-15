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
 * Log creation script for the questionnaire module.
 *
 * @package    logstore_xapi
 * @copyright (C) 2022 Yamaguchi University (gh-cc@mlex.cc.yamaguchi-u.ac.jp)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_questionnaire\item_answered;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

/**
 * This function creates answer log about rate item.
 * @param array $config - array of configurations.
 * @param object $event - object of Moodle event.
 * @param object $question - object of question.
 * @param int $tablename - question table name.
 * @param int $responseid - Id of questionnaire response.
 * @param object $questionnaire - Instance of questionnaire module.
 * @return string - xAPI formatted log statement.
 */
function rate(array $config, \stdClass $event, \stdClass $question, $tablename, $responseid, \stdClass $questionnaire) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $lang = utils\get_course_lang($course);
    $tablename = 'questionnaire_' . $tablename;

    $rankarray = [];
    $itemarray = [];
    $records = $repo->read_records($tablename, ['response_id' => $responseid, 'question_id' => $question->id]);

    foreach ($records as $record) {
        if (!empty($record)) {
            $itemrecord = $repo->read_record_by_id('questionnaire_quest_choice', $record->choice_id);
            if (!empty($itemrecord)) {
                array_push($rankarray, '{' . $record->rankvalue . '}');
                array_push($itemarray, '{' . $itemrecord->content . '}');
            }
        }
    }

    $choices = '';
    $items = '';

    if (!empty($rankarray)) {
        $choices = implode(" ", $rankarray);
        $items = implode(" ", $itemarray);
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
            'id' =>  $config['app_url'].'/mod/questionnaire/questions.php?id=' . $event->contextinstanceid,
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'name' => [
                    $lang => $question->name,
                ],
                'interactionType' => 'choice',
            ],
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => $choices,
            'completion' => !empty($choices),
            "extensions" => [
                "http://learninglocker.net/xapi/cmi/choice/response" => $items,
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
                    utils\get_activity\course_feedback($config, $course, $event->contextinstanceid),
                ],
                'category' => [
                    utils\get_activity\source($config),
                ]
            ],
        ]
    ]];
}
