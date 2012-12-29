<?php
/*	Plugin Name: ChemDoodleWeb
	Plugin URI: http://web.chemdoodle.com/installation/download (original, without modifications)
	Plugin Description: Chemical structure drawing tool for a question-and-answer website
	Plugin Version: 1
	Plugin Date: 2012-06-12
	Plugin Author: Daniela Miao
	Plugin Author URI: Daniela Miao, University of Toronto 2012
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.5.2

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
*/

// Do not allow this file to be accessed directly by the user through typing the url
if ( !defined('QA_VERSION') )
{
	header('Location: ../../');
	exit;
}

// Register the plugin modules: a layer, an event and an override, details regarding what
// each of these plugin modules are can be found on the Q2A website at http://www.question2answer.org/plugins.php
qa_register_plugin_layer('qa-chemdoodle-layer.php', 'ChemDoodle Layer');
qa_register_plugin_module('event', 'qa-chemdoodle-event.php', 'qa_event_chemdoodle', 'Chemdoodle Event');
qa_register_plugin_overrides('qa-chemdoodle-override.php');
