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

namespace src\transformer\events\mod_feedback\item_answered;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

function multichoicerated(array $config, \stdClass $event, \stdClass $feedbackvalue, \stdClass $feedbackitem) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $feedback = $repo->read_record_by_id('feedback', $feedbackitem->feedback);
    $lang = utils\get_course_lang($course);
    $presentedchoices = explode("|", substr($feedbackitem->presentation, 6));
    for ($i = 0; $i < count($presentedchoices); $i = $i + 1) {
        $presentedchoices[$i] = str_replace('\n', '', $presentedchoices[$i]);
    }
    $choices = array_map(function ($presentation, $id) {
        $split = explode('####', $presentation);
        $rating = $split[0];
        $name = $split[1];
        return (object) [
            'rating' => intval($rating),
            'name' => $name,
            'id' => $id,
        ];
    }, $presentedchoices, array_keys($presentedchoices));


    echo "responses = " . PHP_EOL;
    var_dump($choices);
    echo PHP_EOL;

    for ($i = 0; $i < count($choices); $i = $i + 1) {
        $choices[$i]->name = str_replace('\n', '', $choices[$i]->name);
        $choices[$i]->name = utils\get_string_html_removed(trim($choices[$i]->name));
        $choices[$i]->name = utils\get_string_math_removed(trim($choices[$i]->name));
    }

    $selectedchoice = '';
    $resultname = '';
    $resultrating = '';
    if (!empty($choices) && count($choices) >= 1 && intval($feedbackvalue->value) >= 1) {
        $selectedchoice = $choices[intval($feedbackvalue->value) - 1];
        $resultname = $selectedchoice->name;
        $resultrating = $selectedchoice->rating;
    }

    if (!empty($resultname)) {
        $resultname = utils\get_string_html_removed(trim($resultname));
        $resultname = utils\get_string_math_removed(trim($resultname));
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
            'id' => $config['app_url'].'/mod/feedback/edit_item.php?id=' . $feedbackitem->id,
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'name' => [
                    $lang => $feedbackitem->name,
                ],
                'interactionType' => 'choice',
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => $resultname,
            'completion' => $feedbackvalue->value !== '',
            'extensions' => [
                'http://learninglocker.net/xapi/moodle/feedback_item_rating' => $resultrating,
                'http://learninglocker.net/xapi/cmi/choice/response' => $resultname,
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
