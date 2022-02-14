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

namespace src\transformer\utils\get_activity;
defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

/**
 * Callback function for questionnaire module.
 * @param array $config - array of configuration statements.
 * @param object $course - course object.
 * @param int $cmid - Id for context module.
 * @return string - xAPI formatted log statement.
 */
function course_questionnaire(array $config, \stdClass $course, $cmid) {
    return utils\get_activity\course_module(
        $config,
        $course,
        $cmid,
        'http://id.tincanapi.com/activitytype/survey'
    );
}
