<?php

/*
	University of Toronto (c) Daniela Miao

	http://www.question2answer.org/

	File: qa-plugin/ChemDoodleWeb/qa-chemdoodle-override.php
	Version: 1
	Description: This plugin override module is for overriding the function qa_post_html_fields
                    in the file qa-include/qa-app-formats.php. The function controls what is
                    displayed to the user on the "Questions" page, here we want to be able
                    to view the chemical structure drawing associated with post.

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

    // This override makes very little changes to the original function, the modifications
    // can be found under "// Post Content"
	function qa_post_html_fields($post, $userid, $cookieid, $usershtml, $dummy, $options=array())
/*
	Given $post retrieved from database, return array of mostly HTML to be passed to theme layer.
	$userid and $cookieid refer to the user *viewing* the page.
	$usershtml is an array of [user id] => [HTML representation of user] built ahead of time.
	$dummy is a placeholder (used to be $categories parameter but that's no longer needed)
	$options is an array which sets what is displayed (see qa_post_html_defaults() in qa-app-options.php)
	If something is missing from $post (e.g. ['content']), correponding HTML also omitted.
*/
	{
		require_once QA_INCLUDE_DIR.'qa-app-updates.php';
		
		if (isset($options['blockwordspreg']))
			require_once QA_INCLUDE_DIR.'qa-util-string.php';
		
		$fields=array();
		$fields['raw']=$post;
		
	//	Useful stuff used throughout function

		$postid=$post['postid'];
		$isquestion=($post['basetype']=='Q');
		$isanswer=($post['basetype']=='A');
		$isbyuser=qa_post_is_by_user($post, $userid, $cookieid);
		$anchor=urlencode(qa_anchor($post['basetype'], $postid));
		$elementid=isset($options['elementid']) ? $options['elementid'] : $anchor;
		$microformats=@$options['microformats'];
		$isselected=@$options['isselected'];
		
	//	High level information

		$fields['hidden']=@$post['hidden'];
		$fields['tags']='ID="'.qa_html($elementid).'"';

		if ($microformats)
			$fields['classes']='hentry '.($isquestion ? 'question' : ($isanswer ? ($isselected ? 'answer answer-selected' : 'answer') : 'comment'));

	//	Question-specific stuff (title, URL, tags, answer count, category)

		if ($isquestion) {
			if (isset($post['title'])) {
				$fields['url']=qa_q_path_html($postid, $post['title']);

				if (isset($options['blockwordspreg']))
					$post['title']=qa_block_words_replace($post['title'], $options['blockwordspreg']);

				$fields['title']=qa_html($post['title']);
				if ($microformats)
					$fields['title']='<SPAN CLASS="entry-title">'.$fields['title'].'</SPAN>';

				/*if (isset($post['score'])) // useful for setting match thresholds
					$fields['title'].=' <SMALL>('.$post['score'].')</SMALL>';*/
			}

			if (@$options['tagsview'] && isset($post['tags'])) {
				$fields['q_tags']=array();

				$tags=qa_tagstring_to_tags($post['tags']);
				foreach ($tags as $tag) {
					if (isset($options['blockwordspreg']) && count(qa_block_words_match_all($tag, $options['blockwordspreg']))) // skip censored tags
						continue;

					$fields['q_tags'][]=qa_tag_html($tag, $microformats);
				}
			}

			if (@$options['answersview'] && isset($post['acount'])) {
				$fields['answers_raw']=$post['acount'];

				$fields['answers']=($post['acount']==1) ? qa_lang_html_sub_split('main/1_answer', '1', '1')
					: qa_lang_html_sub_split('main/x_answers', number_format($post['acount']));

				$fields['answer_selected']=isset($post['selchildid']);
			}

			if (@$options['viewsview'] && isset($post['views'])) {
				$fields['views_raw']=$post['views'];

				$fields['views']=($post['views']==1) ? qa_lang_html_sub_split('main/1_view', '1', '1') :
					qa_lang_html_sub_split('main/x_views', number_format($post['views']));
			}

			if (@$options['categoryview'] && isset($post['categoryname']) && isset($post['categorybackpath']))
				$fields['where']=qa_lang_html_sub_split('main/in_category_x',
					'<A HREF="'.qa_path_html(@$options['categorypathprefix'].implode('/', array_reverse(explode('/', $post['categorybackpath'])))).
					'" CLASS="qa-category-link">'.qa_html($post['categoryname']).'</A>');
		}

	//	Answer-specific stuff (selection)

		if ($isanswer) {
			$fields['selected']=$isselected;

			if ($isselected)
				$fields['select_text']=qa_lang_html('question/select_text');
		}

	//	Post content

        // here is where the modifications from the override come in
		if (@$options['contentview'] && !empty($post['content'])) {
			$viewer=qa_load_viewer($post['content'], $post['format']);
			
			$fields['content']=$viewer->get_html($post['content'], $post['format'], array(
				'blockwordspreg' => @$options['blockwordspreg'],
				'showurllinks' => @$options['showurllinks'],
				'linksnewwindow' => @$options['linksnewwindow'],
			));

            // if there is an existing MOL file associated with the current post that is about to be displayed, then
            // insert the javascript code needed to create a ViewerCanvas and load the appropriate MOL file
            $molFile = './qa-plugin/ChemDoodleWeb/data/upload/'.$postid.'.mol';
            $molExists = file_exists($molFile);
            if ($molExists === true) {
                $fields['content'] = "<SCRIPT>" .
                    "var viewCanvas".$postid." = new ChemDoodle.ViewerCanvas('viewCanvas".$postid.".', 300, 200);" .
                    "viewCanvas".$postid.".emptyMessage = 'No Data Loaded!';" .
                    "ChemDoodle.io.file.content('./qa-plugin/ChemDoodleWeb/data/upload/".$postid.".mol', function(fileContent){" .
                    'var mol = ChemDoodle.readMOL(fileContent);' .
                    'viewCanvas'.$postid.'.loadMolecule(mol);' .
                    '});' .
                    "</SCRIPT>" .
                    "<BR>" .
                    $fields['content'];
            }

			if ($microformats)
				$fields['content']='<SPAN CLASS="entry-content">'.$fields['content'].'</SPAN>';
			
			$fields['content']='<A NAME="'.qa_html($postid).'"></A>'.$fields['content'];
				// this is for backwards compatibility with any existing links using the old style of anchor
				// that contained the post id only (changed to be valid under W3C specifications)
		}
		
	//	Voting stuff
			
		if (@$options['voteview']) {
			$voteview=$options['voteview'];
		
		//	Calculate raw values and pass through
		
			$upvotes=(int)@$post['upvotes'];
			$downvotes=(int)@$post['downvotes'];
			$netvotes=(int)($upvotes-$downvotes);
			
			$fields['upvotes_raw']=$upvotes;
			$fields['downvotes_raw']=$downvotes;
			$fields['netvotes_raw']=$netvotes;

		//	Create HTML versions...
			
			$upvoteshtml=qa_html($upvotes);
			$downvoteshtml=qa_html($downvotes);

			if ($netvotes>=1)
				$netvoteshtml='+'.qa_html($netvotes);
			elseif ($netvotes<=-1)
				$netvoteshtml='&ndash;'.qa_html(-$netvotes);
			else
				$netvoteshtml='0';
				
		//	...with microformats if appropriate

			if ($microformats) {
				$netvoteshtml.='<SPAN CLASS="votes-up"><SPAN CLASS="value-title" TITLE="'.$upvoteshtml.'"></SPAN></SPAN>'.
					'<SPAN CLASS="votes-down"><SPAN CLASS="value-title" TITLE="'.$downvoteshtml.'"></SPAN></SPAN>';
				$upvoteshtml='<SPAN CLASS="votes-up">'.$upvoteshtml.'</SPAN>';
				$downvoteshtml='<SPAN CLASS="votes-down">'.$downvoteshtml.'</SPAN>';
			}
			
		//	Pass information on vote viewing
		
		//	$voteview will be one of:
		//	updown, updown-disabled-level, updown-disabled-page, updown-uponly-level
		//	net, net-disabled-level, net-disabled-page, net-uponly-level
				
			$fields['vote_view']=(substr($voteview, 0, 6)=='updown') ? 'updown' : 'net';
			
			$fields['upvotes_view']=($upvotes==1) ? qa_lang_html_sub_split('main/1_liked', $upvoteshtml, '1')
				: qa_lang_html_sub_split('main/x_liked', $upvoteshtml);
	
			$fields['downvotes_view']=($downvotes==1) ? qa_lang_html_sub_split('main/1_disliked', $downvoteshtml, '1')
				: qa_lang_html_sub_split('main/x_disliked', $downvoteshtml);
			
			$fields['netvotes_view']=(abs($netvotes)==1) ? qa_lang_html_sub_split('main/1_vote', $netvoteshtml, '1')
				: qa_lang_html_sub_split('main/x_votes', $netvoteshtml);
		
		//	Voting buttons
			
			$fields['vote_tags']='ID="voting_'.qa_html($postid).'"';
			$onclick='onClick="return qa_vote_click(this);"';
			
			if ($fields['hidden']) {
				$fields['vote_state']='disabled';
				$fields['vote_up_tags']='TITLE="'.qa_lang_html($isanswer ? 'main/vote_disabled_hidden_a' : 'main/vote_disabled_hidden_q').'"';
				$fields['vote_down_tags']=$fields['vote_up_tags'];
			
			} elseif ($isbyuser) {
				$fields['vote_state']='disabled';
				$fields['vote_up_tags']='TITLE="'.qa_lang_html($isanswer ? 'main/vote_disabled_my_a' : 'main/vote_disabled_my_q').'"';
				$fields['vote_down_tags']=$fields['vote_up_tags'];
				
			} elseif (strpos($voteview, '-disabled-')) {
				$fields['vote_state']=(@$post['uservote']>0) ? 'voted_up_disabled' : ((@$post['uservote']<0) ? 'voted_down_disabled' : 'disabled');
				
				if (strpos($voteview, '-disabled-page'))
					$fields['vote_up_tags']='TITLE="'.qa_lang_html('main/vote_disabled_q_page_only').'"';
				else
					$fields['vote_up_tags']='TITLE="'.qa_lang_html('main/vote_disabled_level').'"';
					
				$fields['vote_down_tags']=$fields['vote_up_tags'];

			} elseif (@$post['uservote']>0) {
				$fields['vote_state']='voted_up';
				$fields['vote_up_tags']='TITLE="'.qa_lang_html('main/voted_up_popup').'" NAME="'.qa_html('vote_'.$postid.'_0_'.$elementid).'" '.$onclick;
				$fields['vote_down_tags']=' ';

			} elseif (@$post['uservote']<0) {
				$fields['vote_state']='voted_down';
				$fields['vote_up_tags']=' ';
				$fields['vote_down_tags']='TITLE="'.qa_lang_html('main/voted_down_popup').'" NAME="'.qa_html('vote_'.$postid.'_0_'.$elementid).'" '.$onclick;
				
			} else {
				$fields['vote_up_tags']='TITLE="'.qa_lang_html('main/vote_up_popup').'" NAME="'.qa_html('vote_'.$postid.'_1_'.$elementid).'" '.$onclick;
				
				if (strpos($voteview, '-uponly-level')) {
					$fields['vote_state']='up_only';
					$fields['vote_down_tags']='TITLE="'.qa_lang_html('main/vote_disabled_down').'"';
				
				} else {
					$fields['vote_state']='enabled';
					$fields['vote_down_tags']='TITLE="'.qa_lang_html('main/vote_down_popup').'" NAME="'.qa_html('vote_'.$postid.'_-1_'.$elementid).'" '.$onclick;
				}
			}
		}
		
	//	Flag count
	
		if (@$options['flagsview'] && @$post['flagcount'])
			$fields['flags']=($post['flagcount']==1) ? qa_lang_html_sub_split('main/1_flag', '1', '1')
				: qa_lang_html_sub_split('main/x_flags', $post['flagcount']);
	
	//	Created when and by whom
		
		$fields['meta_order']=qa_lang_html('main/meta_order'); // sets ordering of meta elements which can be language-specific
		
		if (@$options['whatview'] ) {
			$fields['what']=qa_lang_html($isquestion ? 'main/asked' : ($isanswer ? 'main/answered' : 'main/commented'));
				
			if (@$options['whatlink'] && !$isquestion)
				$fields['what_url']=qa_path_html(qa_request(), array('show' => $postid), null, null, qa_anchor($post['basetype'], $postid));
		}
		
		if (isset($post['created']) && @$options['whenview']) {
			$fields['when']=qa_when_to_html($post['created'], @$options['fulldatedays']);
			
			if ($microformats)
				$fields['when']['data']='<SPAN CLASS="published"><SPAN CLASS="value-title" TITLE="'.gmdate('Y-m-d\TH:i:sO', $post['created']).'"></SPAN>'.$fields['when']['data'].'</SPAN>';
		}
		
		if (@$options['whoview']) {
			$fields['who']=qa_who_to_html($isbyuser, @$post['userid'], $usershtml, @$options['ipview'] ? @$post['createip'] : null, $microformats);
			
			if (isset($post['points'])) {
				if (@$options['pointsview'])
					$fields['who']['points']=($post['points']==1) ? qa_lang_html_sub_split('main/1_point', '1', '1')
						: qa_lang_html_sub_split('main/x_points', qa_html(number_format($post['points'])));
				
				if (isset($options['pointstitle']))
					$fields['who']['title']=qa_get_points_title_html($post['points'], $options['pointstitle']);
			}
				
			if (isset($post['level']))
				$fields['who']['level']=qa_html(qa_user_level_string($post['level']));
		}

		if ((!QA_FINAL_EXTERNAL_USERS) && (@$options['avatarsize']>0))
			$fields['avatar']=qa_get_user_avatar_html(@$post['flags'], @$post['email'], @$post['handle'],
				@$post['avatarblobid'], @$post['avatarwidth'], @$post['avatarheight'], $options['avatarsize']);

	//	Updated when and by whom
		
		if (
			@$options['updateview'] && isset($post['updated']) &&
			(($post['updatetype']!=QA_UPDATE_SELECTED) || $isselected) && // only show selected change if it's still selected
			( // otherwise check if one of these conditions is fulfilled...
				(!isset($post['created'])) || // ... we didn't show the created time (should never happen in practice)
				($post['hidden'] && ($post['updatetype']==QA_UPDATE_VISIBLE)) || // ... the post was hidden as the last action
				(isset($post['closedbyid']) && ($post['updatetype']==QA_UPDATE_CLOSED)) || // ... the post was closed as the last action
				(abs($post['updated']-$post['created'])>300) || // ... or over 5 minutes passed between create and update times
				($post['lastuserid']!=$post['userid']) // ... or it was updated by a different user
			)
		) {
			switch ($post['updatetype']) {
				case QA_UPDATE_TYPE:
				case QA_UPDATE_PARENT:
					$langstring='main/moved';
					break;
					
				case QA_UPDATE_CATEGORY:
					$langstring='main/recategorized';
					break;

				case QA_UPDATE_VISIBLE:
					$langstring=$post['hidden'] ? 'main/hidden' : 'main/reshown';
					break;
					
				case QA_UPDATE_CLOSED:
					$langstring=isset($post['closedbyid']) ? 'main/closed' : 'main/reopened';
					break;
					
				case QA_UPDATE_TAGS:
					$langstring='main/retagged';
					break;
				
				case QA_UPDATE_SELECTED:
					$langstring='main/selected';
					break;
				
				default:
					$langstring='main/edited';
					break;
			}
			
			$fields['what_2']=qa_lang_html($langstring);
			
			if (@$options['whenview']) {
				$fields['when_2']=qa_when_to_html($post['updated'], @$options['fulldatedays']);
				
				if ($microformats)
					$fields['when_2']['data']='<SPAN CLASS="updated"><SPAN CLASS="value-title" TITLE="'.gmdate('Y-m-d\TH:i:sO', $post['updated']).'"></SPAN>'.$fields['when_2']['data'].'</SPAN>';
			}
			
			if (isset($post['lastuserid']) && @$options['whoview'])
				$fields['who_2']=qa_who_to_html(isset($userid) && ($post['lastuserid']==$userid), $post['lastuserid'], $usershtml, @$options['ipview'] ? $post['lastip'] : null, false);
		}
		
	//	That's it!

		return $fields;
	}
	
/*
	Omit PHP closing tag to help avoid accidental output
*/