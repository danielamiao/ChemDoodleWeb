<?php

/*
	University of Toronto (c) Daniela Miao

	http://www.question2answer.org/

	File: qa-plugin/ChemDoodleWeb/upload.php
	Version: 1
	Description: This php file is not a plugin module, but simply contains a helper function
                    that is used to save the user's chemical structure drawing on the server
                    all files are saved with the name temp.mol. This file will be renamed
                    later according to the post ID.

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

// get the file content from 'data' first
$fileContent=$_POST["data"];

// save the MOL file to the plugin data/upload directory
$myFile = "data/upload/temp.mol";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $fileContent);
fclose($fh);

echo "OK!";