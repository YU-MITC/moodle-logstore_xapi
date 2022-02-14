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
use src\transformer\events\mod_questionnaire\item_answered as item_answered;

/**
 * This function calls callback function according to event type.
 * @param array $config - array of configuration information.
 * @param object $event - object of Moodle event.
 * @return string - xAPI formatted log statement.
 */
function handler(array $config, \stdClass $event) {
    $repo = $config['repo'];

    $questionnaireid = null;
    $submission = null;

    $other = trim($event->other);

    if (strpos($other, "questionnaireid") !== false) {
        $other = substr($other, strpos($other, "questionnaireid") + 16);
        $other = substr($other, strpos($other, "\"") + 1);
        $other = substr($other, 0, strpos($other, "\""));
        $questionnaireid  = $other;

        $submissionarray = $repo->read_records('questionnaire_response',
                                               [
                                                'questionnaireid' => $questionnaireid,
                                                'userid' => $event->userid,
                                                'submitted' => $event->timecreated
                                               ]
                                              );
        if (empty($submissionarray)) {
            return [];
        }

        $submissionarray = array_slice($submissionarray, -1, 1);
        $submission = $submissionarray[0];

        if ($questionnaireid !== null && strcmp($submission->complete, "y") === 0) {

            $questionnaire = $repo->read_record_by_id('questionnaire', $questionnaireid);
            $questionarray = $repo->read_records('questionnaire_question', ['surveyid' => $questionnaire->sid]);

            return array_merge(
                attempt_submitted($config, $event, $questionnaireid),
                array_reduce($questionarray, function ($result, $question) use ($config, $event, $submission, $questionnaire) {
                    return array_merge($result, item_answered\handler($config, $event, $question, $submission->id, $questionnaire));
                }, [])
            );
       }
    }

    return null;
}
