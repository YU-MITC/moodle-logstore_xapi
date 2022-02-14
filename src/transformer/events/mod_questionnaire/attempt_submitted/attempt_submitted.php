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

namespace src\transformer\events\mod_questionnaire\attempt_submitted;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

/**
 * This function creates attempt submitted log about questionnaire module.
 * @param array $config - array of configurations.
 * @param object $event - object of Moodle event.
 * @param int $questionnaireid - Id of questionnaire module instance.
 * @return string - xAPI formatted log statement.
 */
function attempt_submitted(array $config, \stdClass $event, $questionnaireid) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $lang = utils\get_course_lang($course);
    $questionnaire = $repo->read_record_by_id('questionnaire', $questionnaireid);

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://activitystrea.ms/schema/1.0/submit',
            'display' => [
                $lang => 'submitted'
            ],
        ],
        'object' => utils\get_activity\course_questionnaire($config,
                                                            $course,
                                                            $event->contextinstanceid,
                                                            $questionnaire->name),
        'timestamp' => utils\get_event_timestamp($event),
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'grouping' => [
                    utils\get_activity\site($config),
                    utils\get_activity\course($config, $course),
                ],
                'category' => [
                    utils\get_activity\source($config),
                ]
            ],
        ],
    ]];
}
