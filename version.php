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
 * A Moodle plugin to send xAPI statements to an LRS using events in the Moodle logstore.
 *
 * @package    logstore_xapi
 * @copyright (C) 2020 Ryan Smith, jerrett fowler, and David Pesce
 * @copyright (C) 2022-2023 Yamaguchi University (gh-cc@mlex.cc.yamaguchi-u.ac.jp)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'logstore_xapi';
$plugin->version = 2023071900;
$plugin->release = 'v4.9.0 (YU modified)';
$plugin->supported = [39, 402];
$plugin->requires = 2020061500;
$plugin->maturity = MATURITY_STABLE;

