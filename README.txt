Copyright University of Toronto 2012

Created by: Daniela Miao
Contact: daniela.miao@utoronto.ca
Date: June 22nd, 2012

This is a plugin to the open source software Questions2Answer, used to insert the "sketcher" tool
into the Questions2Answer (Q2A) web collaboration space. This allows University of Toronto 
graduate students in the Chemistry Department to easily ask each other questions and discuss 
problems online.

The contents of this folder is the same as the Web Components of ChemDoodle software available 
for download at http://web.chemdoodle.com/installation/download, with the following modifications
made by the author:

1. Addition of files
	- qa-plugin.php: a file used to register this plugin into the Q2A system
	- qa-chemdoodle-layer.php: a file used to change the layout of the Q2A system to allow the
				insertion of the ChemDoodle Sketcher tool on the "Ask a 
				Question" and "Answer Question" web pages.
	- qa-chemdoodle-event.php: a file used to dictate actions the Q2A system needs to perform
				upon a question, an answer or a comment is posted. An 
				example of these actions is to rename the appropriate MOL
				file so that the correct MOL file is associated with each post.
	- qa-chemdoodle-override.php: a file used to override one function in the qa-app-formats.php
				file, found inside the qa-include directory. This function is 
				overriden in order to make sure each completed chemical 
				structure drawing (MOL file) is correctly displayed on the
				web pages.
	- upload.php: a file used to upload complete chemical structure drawings (MOL files) made by
				the users to the data/upload directory.

2. Addition of directory:
	- data/upload: a directory that stores all chemical structure drawings (MOL files) for this web 
				collaboration space.
