<?php

/*
	University of Toronto (c) Daniela Miao

	http://www.question2answer.org/
	
	File: qa-plugin/ChemDoodleWeb/qa-chemdoodle-event.php
	Version: 1
	Description: This plugin event module is for storing ChemDoodle chemical structure
                    drawings (in format of MOL files) with appropriate postids in the
                    data/upload directory.

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

class qa_event_chemdoodle {

    function process_event($event, $userid, $handle, $cookieid, $params)
    {
        /*
        The file creation below is for debugging purposes only, to see where the current dir is
        $myFile = "TEST.mol";
        $fh = fopen($myFile, 'w') or die("can't open file");
        fwrite($fh, "THIS IS A TEST");
        fclose($fh);
        */

        // this function is straightforward, whenever a question or an answer is posted,
        // rename the temporary MOL file, temp.mol, to $postid.mol, where $postid is the
        // ID of the current post
        if ($event == 'q_post' or $event == 'a_post') {
            $tempFile = "qa-plugin/ChemDoodleWeb/data/upload/temp.mol";
            $tempExists = file_exists($tempFile);
            if ($tempExists === true) {
                $newName = "qa-plugin/ChemDoodleWeb/data/upload/" . $params['postid'] . ".mol";
                rename($tempFile, $newName);
            }
        }

        // whenever a question or an answer is deleted, delete the corresponding MOL file
        // from the server
        elseif ($event == 'q_delete' or $event == 'a_delete') {
            $postid = $params['postid'];
            $fileName = "qa-plugin/ChemDoodleWeb/data/upload/" . $postid . ".mol";
            $molExists = file_exists($fileName);
            if ($molExists === true) {
                unlink($fileName);
            }
        }

        //  whenever a question or an answer is edited, rename the temporary MOL file,
        // temp.mol, to $postid.mol, where $postid is the ID of the edited post
        elseif ($event == 'q_edit' or $event == 'a_edit') {
            $postid = $params['postid'];
            $fileName = "qa-plugin/ChemDoodleWeb/data/upload/" . $postid . ".mol";
            $tempFile = "qa-plugin/ChemDoodleWeb/data/upload/temp.mol";
            $tempExists = file_exists($tempFile);
            if ($tempExists === true) {
                rename($tempFile, $fileName);
            }
        }
    }
}


/*
    Omit PHP closing tag to help avoid accidental output
*/