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
 * This function calls callback function according to item type.
 * @param array $config - array of configuration information.
 * @param object $event - object of Moodle event.
 * @param object $question - object of question.
 * @param int $reponseid - Id of questionnaire response.
 * @param object $questionnaire - Instance of questionnaire module.
 * @return string - xAPI formatted log statement.
 */
function handler(array $config, \stdClass $event, \stdClass $question, $responseid, \stdClass $questionnaire) {
    $repo = $config['repo'];
    $typerecord = $repo->read_record('questionnaire_question_type', ['typeid' => $question->type_id]);

    switch ($typerecord->type) {
        case 'Yes/No':
            return yesno($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        case 'Check Boxes';
            return checkboxes($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        case 'Radio Buttons':
        case 'Dropdown Box':
            return respsingle($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        case 'Rate (scale 1..5)':
            return rate($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        case 'Date':
            return dateresp($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        case 'Numeric':
            return numeric($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        case 'Text Box':
        case 'Essay Box':
            return resptext($config, $event, $question, $typerecord->response_table, $responseid, $questionnaire);
        default:
            return [];
    }
}
