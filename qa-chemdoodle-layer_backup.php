<?php

/*
	University of Toronto (c) Daniela Miao

	http://www.question2answer.org/

	File: qa-plugin/ChemDoodleWeb/qa-chemdoodle-layer.php
	Version: 1
	Description: This plugin layer is used to change the layout of the default Questions2Answer
                 web pages. This incorporates the ChemDoodle Sketcher tool on the web pages to
                 allow users to insert chemical structure drawings with their questions/answer.

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

class qa_html_theme_layer extends qa_html_theme_base
{
    // add to head_custom since it was empty in the original function anyway
    // these additions modify the <HEAD> section of the appropriate html pages
	function head_custom()
	{
        // call the parent head_custom in case the function becomes non-empty in future
        // releases of Q2A
		parent::head_custom();

		$tmpl = array( 'ask', 'question' );

        // if this is not a "Ask a Question" or "Answer Question" page, do nothing
        if ( !in_array($this->template, $tmpl) )
			return;

        // otherwise, include the javascript source for the ChemDoodleWeb tool
		$this->output_raw(
			'<!-- Inserting ChemDoodle Script Source here -->'. "\n\t\t" .
			'<SCRIPT SRC="' . qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'install/ChemDoodleWeb-libs.js').
			'" TYPE="text/javascript"></SCRIPT>' . "\n\t\t" .
			'<SCRIPT SRC="' . qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'install/ChemDoodleWeb.js').
			'" TYPE="text/javascript"></SCRIPT>' . "\n\t\t" .
			'<SCRIPT SRC="' . qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'install/sketcher/jquery-ui-1.8.7.custom.min.js').
			'" TYPE="text/javascript"></SCRIPT>' . "\n\t\t" .
			'<SCRIPT SRC="' . qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'install/sketcher/ChemDoodleWeb-sketcher.js').
			'" TYPE="text/javascript"></SCRIPT>' . "\n\t\t" .
			'<LINK REL="stylesheet" href="' . qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'install/ChemDoodleWeb.css').
			'" TYPE="text/css">' . "\n\t\t" . 
			'<LINK REL="stylesheet" href="' . qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'install/sketcher/jquery-ui-1.8.7.custom.css').
			'" TYPE="text/css">'
		);

        // if the page is the "Ask a Question" page, find the completed drawing, invoke the
        // upload script to save the drawing on the server, then submit the question form as
        // usual
        if ($this->template === 'ask') {
            $this->output(
                '<SCRIPT>',
                'function mySubmit() {',
                '// assign our form to object form',
                'var form = document.forms.ask;',
                '// take this out if you want',
                'var string = ChemDoodle.writeMOL(sketcher.getMolecule());',
                "var emptyMol = 'Molecule from ChemDoodle Web Components\\n\\nhttp://www.ichemlabs.com\\n  1  0  0  0  0  0            999 v2000\\n    0.0000    0.0000    0.0000 C   0  0  0  0  0  0\\nM  END';",
                'if (string != emptyMol){', // if the drawing is not empty
                "$.post('./qa-plugin/ChemDoodleWeb/upload.php', {data: string }, function(result) {});",
                '}',
                '// submit the form',
                'form.submit();',
                '}',
                '</SCRIPT>'
            );
        }

        // if the page is the "Answer Questions" page, find the completed drawing, upload, then
        // do nothing as the original answer form will invoke a "submit answer" function
        elseif ($this->template === 'question') {
            $this->output(
                '<SCRIPT>',
                'function mySubmit() {',
                '// take this out if you want',
                'var string = ChemDoodle.writeMOL(sketcher.getMolecule());',
                "var emptyMol = 'Molecule from ChemDoodle Web Components\\n\\nhttp://www.ichemlabs.com\\n  1  0  0  0  0  0            999 v2000\\n    0.0000    0.0000    0.0000 C   0  0  0  0  0  0\\nM  END';",
                'if (string != emptyMol)', // if the drawing is not empty
                "$.post('./qa-plugin/ChemDoodleWeb/upload.php', {data: string }, function(result) {});",
                '}',
                '</SCRIPT>'
            );
        }
	}

    // modify the form_field_rows function in order to change the layout of the "Ask a
    // Question" and "Answer Questions" page. For instance, instead of having just a text
    // box for the users to type their questions in, there also needs to be a ChemDoodle
    // Sketcher tool displayed for the user to make chemical structure drawings
	function form_field_rows($form, $columns, $field)
	{
		$tmpl = array( 'ask', 'question' );

        // only do this if the page is "Ask a Question" or "Answer Questions"
		if (in_array($this->template, $tmpl))
		{
            // please uncomment below as needed when debugging
			// $this->output('<TR>This 1 debug message should show up on the following pages: Ask a Question, Answer Questions and Comment on Questions/Answers</TR>');
			// print_r ($field); // Debugging for the contents of the field

			$uploadRoot = './qa-plugin/ChemDoodleWeb/data/upload/';

            // if this is the "Ask a Question page, do the following
            if ($this->template == 'ask') {

                // if we have reached the part of the html where a TEXTAREA is to be inserted,
                // ie. the field_key of the context array is 'content', then this is where we
                // need to insert the ChemDoodle Sketcher tool for display to the user. This
                // tool will appear BEFORE the textarea where the user types the question
                if (strpos($this->context['field_key'] , 'content') === 0)
                {
                    // uncomment below when debugging
                    // $this->output('<TR>This 2 debug message should show up on the following pages: Ask a Question

                    // insert all the javascript lines in order to create the ChemDoodle
                    // Sketcher tool for display to the user
                    $this->output(
                        '<TR>',
                        '<TD CLASS="qa-form-tall-data">',
                        '<SCRIPT>',
                        '// changes the default JMol color of hydrogen to black so it appears on white backgrounds',
                        "ChemDoodle.ELEMENT['H'].jmolColor = 'black';",
                        '// darkens the default JMol color of sulfur so it appears on white backgrounds',
                        "ChemDoodle.ELEMENT['S'].jmolColor = '#B9A130';",
                        '// initializes the SketcherCanvas; make sure to set the path to the icons correctly!',
                        "var sketcher = new ChemDoodle.SketcherCanvas('sketcher', 540, 250, './qa-plugin/ChemDoodleWeb/install/sketcher/icons/', ChemDoodle.featureDetection.supports_touch(), false);",
                        '// sets terminal carbon labels to display',
                        'sketcher.specs.atoms_displayTerminalCarbonLabels_2D = true;',
                        '// sets atom labels to be colored by JMol colors, which are easy to recognize',
                        'sketcher.specs.atoms_useJMOLColors = true;',
                        '// the following two settings add overlap clear widths, so that some depth is introduced to overlapping bonds',
                        'sketcher.specs.bonds_clearOverlaps_2D = true;',
                        'sketcher.specs.bonds_overlapClearWidth_2D = 2;'
                    );

                    $tempFile = $uploadRoot . "temp.mol";

                    // if the temp.mol does not exist, it means this is a new drawing that the user
                    // is starting on. At this point, we need to repaint the sketcher to make sure
                    // it's blank
                    if (!file_exists($tempFile)) {
                        $this->output(
                            '// the component needs to be repainted here because we do not call the <em>Canvas.loadMolecule()</em> function',
                            'sketcher.repaint();',
                            '</SCRIPT>',
                            '</TD>',
                            '</TR>'
                        );
                    }

                    // if the temp.mol file exists, that means the user is currently working on
                    // a chemical structure drawing which has not been posted to a question yet,
                    // load this drawing to allow the user to finish working on it
                    else {
                         $this->output(
                            "ChemDoodle.io.file.content('./qa-plugin/ChemDoodleWeb/data/upload/temp.mol', function(fileContent){",
                            'var mol = ChemDoodle.readMOL(fileContent);' ,
                            'sketcher.loadMolecule(mol);' ,
                            '});' ,
                            '</SCRIPT>',
                            '</TD>',
                            '</TR>'
                         );
                    }
                }
            }

            // if this page is the "Answer Questions" page, do the following
            elseif ($this->template == 'question') {
                $comment = preg_match("/.+c\d+_content.+/", $field['tags']);
                $new_answer = preg_match("/.+a_content.+/", $field['tags']);
                $edit_answer = preg_match("/.+a\d+_content.+/", $field['tags'], $match);

                // if this part of the html is a TEXTAREA for the user to type in their
                // answer, NOT a comment, then we need to insert the ChemDoodle Sketcher
                // tool before this TEXTAREA. The TEXTAREA is indicated by the array context,
                // where the value of the 'field_key' key is 'content'
                if ((strpos($this->context['field_key'] , 'content') === 0) and $comment === 0)
                {
                    // uncomment below when debugging
                    // $this->output('<TR>This 3 debug message should show up on the following pages: Answer Questions and Comment on Questions/Answers</TR>');
                    // print_r($this->content);

                    // insert all the javascript lines in order to create the ChemDoodle
                    // Sketcher tool for display to the user
                    $this->output(
                        '<TR>',
                        '<TD CLASS="qa-form-tall-data">',
                        '<SCRIPT>',
                        '// changes the default JMol color of hydrogen to black so it appears on white backgrounds',
                        "ChemDoodle.ELEMENT['H'].jmolColor = 'black';",
                        '// darkens the default JMol color of sulfur so it appears on white backgrounds',
                        "ChemDoodle.ELEMENT['S'].jmolColor = '#B9A130';",
                        '// initializes the SketcherCanvas; make sure to set the path to the icons correctly!',
                        "var sketcher = new ChemDoodle.SketcherCanvas('sketcher', 540, 250, './qa-plugin/ChemDoodleWeb/install/sketcher/icons/', ChemDoodle.featureDetection.supports_touch(), false);",
                        '// sets terminal carbon labels to display',
                        'sketcher.specs.atoms_displayTerminalCarbonLabels_2D = true;',
                        '// sets atom labels to be colored by JMol colors, which are easy to recognize',
                        'sketcher.specs.atoms_useJMOLColors = true;',
                        '// the following two settings add overlap clear widths, so that some depth is introduced to overlapping bonds',
                        'sketcher.specs.bonds_clearOverlaps_2D = true;',
                        'sketcher.specs.bonds_overlapClearWidth_2D = 2;'
                    );

                    // we need to find out the postid of the post that this drawing is
                    // associated with. If this is a brand new answer, the postid is the
                    // question id. If this is editing an existing answer, that id is returned
                    // instead. Otherwise, just return the question id by default
                    if ($edit_answer) {
                        preg_match("/\d+/", $match[0], $num_match);
                        $postid = $num_match[0];
                    }
                    elseif ($new_answer) {
                        $postid = $this->content['q_view']['raw']['postid'];
                    }
                    else {
                        $postid = $this->content['q_view']['raw']['postid'];
                    }

                    $fileName = $uploadRoot.$postid.".mol";
                    $tempFile = $uploadRoot."temp.mol";

                    // if the temp.mol does not exist and there is also no existing drawing
                    // associated with the current postid (whether it is the question or the
                    // answer, that means we need to repaint the sketcher to make sure
                    // it's blank
                    if ((!file_exists($tempFile)) and (!file_exists($fileName))) {
                        $this->output(
                            '// the component needs to be repainted here because we do not call the <em>Canvas.loadMolecule()</em> function',
                            'sketcher.repaint();',
                            '</SCRIPT>',
                            '</TD>',
                            '</TR>'
                        );
                    }

                    // if the temp.mol file exists, that means the user is currently working on
                    // a chemical structure drawing which has not been posted as an answer yet,
                    // load this drawing to allow the user to finish working on it
                    elseif (file_exists($tempFile)) {
                        $this->output(
                            "ChemDoodle.io.file.content('./qa-plugin/ChemDoodleWeb/data/upload/temp.mol', function(fileContent){",
                            'var mol = ChemDoodle.readMOL(fileContent);' ,
                            'sketcher.loadMolecule(mol);' ,
                            '});' ,
                            '</SCRIPT>',
                            '</TD>',
                            '</TR>'
                        );
                    }

                    // otherwise, if there is an existing MOL file associated with the current postid
                    // note this can be a drawing in the question, OR a drawing in the existing answers.
                    // load that existing MOL file into the sketcher so users can modify it
                    else {
                        $this->output(
                            "ChemDoodle.io.file.content('".$fileName."', function(fileContent){",
                            'var mol = ChemDoodle.readMOL(fileContent);' ,
                            'sketcher.loadMolecule(mol);' ,
                            '});' ,
                            '</SCRIPT>',
                            '</TD>',
                            '</TR>'
                        );
                    }
                }
            }

		}

        // call the parent function for all other pages
		parent::form_field_rows($form, $columns, $field);
	}

    // modify the form_button_data function in order to change the behaviour of the "submit"
    // buttons on the "Ask a Question" and "Answer Questions" page. For instance, instead of
    // just submitting the form when asking a question, a javascript also needs to be called
    // in order to save the chemical structure drawing from the ChemDoodle Sketcher tool to
    // the server
    function form_button_data($button, $key, $style)
    {
        $baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
        $hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;

        // if this is the "Ask a Question" page and the button is "Ask!", modify the
        // submit function of this button to call the mySubmit javascript function
        // (found in the <HEAD> we modified in head_custom) before submitting the form
        // for php processing
        if($this->template === 'ask' and @$button['label'] === 'Ask!') {
            @$button['tags'] = 'onClick=" javascript: mySubmit();"';
            $this->output('<INPUT'.rtrim(' '.@$button['tags']).' VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="submit"'.
            (isset($style) ? (' CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"') : '').'/>');
        }

        // if this is the "Answer Questions" page and the button is "Add answer", modify the
        // submit function of this button to call the mySubmit javascript function
        // then call the original javascript function for submitting the answer
        elseif($this->template === 'question' and @$button['label'] === 'Add answer') {
            @$button['tags'] = 'onClick=" javascript: mySubmit(); return qa_submit_answer('.$this->content['q_view']['raw']['postid'].');"';
            //print_r(@$button['tags']); //Debugging
            $this->output('<INPUT'.rtrim(' '.@$button['tags']).' VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="submit"'.
                (isset($style) ? (' CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"') : '').'/>');
        }

        // if this is the "Answer Questions" page and the button is "Save Changes", it
        // means the user is editing the question or an answer, modify the submit function
        // of this button to call the mySubmit javascript function before the form submits
        // the text changes for php processing
        elseif($this->template === 'question' and @$button['label'] === 'Save Changes') {
            @$button['tags'] = 'onClick=" javascript: mySubmit();"';
            //print_r(@$button['tags']); //Debugging
            $this->output('<INPUT'.rtrim(' '.@$button['tags']).' VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="submit"'.
                (isset($style) ? (' CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"') : '').'/>');
        }

        // otherwise, don't change anything
        else {
            $this->output('<INPUT'.rtrim(' '.@$button['tags']).' VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="submit"'.
            (isset($style) ? (' CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"') : '').'/>');
        }
    }
}