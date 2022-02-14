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
 * Log creation script for the Kaltura media assignment module.
 *
 * @package    logstore_xapi
 * @copyright (C) 2022 Yamaguchi University (gh-cc@mlex.cc.yamaguchi-u.ac.jp)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_kalmediaassign;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;


/**
 * This function creates grades updated log about Kaltura media assignment module.
 * @param array $config - array of configurations.
 * @param object $event - object of Moodle event.
 * @return string - xAPI formatted log statement.
 */
function grades_updated(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $grade = $repo->read_record_by_id($event->objecttable, $event->objectid);
    $user = $repo->read_record_by_id('user', $grade->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $instructor = $repo->read_record_by_id('user', $event->userid);
    $assignment = $repo->read_record_by_id('kalmediaassign', $grade->mediaassignid);
    $lang = utils\get_course_lang($course);

    $gradecomment = null;
    try {
        $gradecomment = $repo->read_record('kalmediaassign_submission', [
            'mediaassignid' => $grade->mediaassignid,
            'id' => $grade->id
        ])->submissioncomment;
        $gradecomment =  utils\get_string_html_removed($gradecomment);
    } catch (\Exception $e) {
        $gradecomment = null;
    }

    $gradeitems = $repo->read_record('grade_items', [
        'itemmodule' => 'kalmediaassign',
        'iteminstance' => $grade->mediaassignid
    ]);

    $scoreraw = (float) ($grade->grade ?: 0);
    $scoremin = (float) ($gradeitems->grademin ?: 0);
    $scoremax = (float) ($gradeitems->grademax ?: 0);
    $scorepass = (float) ($gradeitems->gradepass ?: null);

    $success = false;

    if ($scoreraw >= $scorepass) {
        $success = true;
    }

    $statement = [
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/scored',
            'display' => [
                $lang => 'attained grade for'
            ],
        ],
        'object' => utils\get_activity\course_kalmediaassign($config, $event->contextinstanceid, $assignment->name, $lang),
        'result' => [
            'score' => [
                'raw' => $scoreraw
            ],
            'completion' => true,
            'success' => $success
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'context' => [
            'instructor' => utils\get_user($config, $instructor),
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

    if (!is_null($gradecomment)) {
        $statement['result']['response'] = $gradecomment;
    }

    // Only include min score if raw score is valid for that min.
    if ($scoreraw >= $scoremin) {
        $statement['result']['score']['min'] = $scoremin;
    }
    // Only include max score if raw score is valid for that max.
    if ($scoreraw <= $scoremax) {
        $statement['result']['score']['max'] = $scoremax;
    }
    // Calculate scaled score as the distance from zero towards the max (or min for negative scores).
    if ($scoreraw >= 0) {
        $statement['result']['score']['scaled'] = $scoreraw / $scoremax;
    }

    return [$statement];
}
