<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of postslistOptions, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!$core->auth->check('admin',$core->blog->id)) { return; }

$core->addBehavior('adminPostsActionsCombo',array('behaviorsPostlistOptions','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActionsContent',array('behaviorsPostlistOptions','adminPostsActionsContent'));
$core->addBehavior('adminPostsActions',array('behaviorsPostlistOptions','adminPostsActions'));

class behaviorsPostlistOptions
{
	public static function adminPostsActionsCombo($args)
	{
		if (!$GLOBALS['core']->auth->check('admin',$GLOBALS['core']->blog->id)) return;
		
		$args[0][__('Comments')][__('Mark as opened')] = 'commentsopen';
		$args[0][__('Comments')][__('Mark as closed')] = 'commentsclose';
		$args[0][__('Comments')][__('Delete all comments')] = 'commentsdelete';
		$args[0][__('Trackbacks')][__('Mark as opened')] = 'trackbacksopen';
		$args[0][__('Trackbacks')][__('Mark as closed')] = 'trackbacksclose';
		$args[0][__('Trackbacks')][__('Delete all trackbacks')] = 'trackbacksdelete';
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		$allow = array(
			'commentsdelete' => __('Are you sure you want to delete all comments?'),
			'trackbacksdelete' => __('Are you sure you want to delete all trackbacks?')
		);
		
		if (!isset($allow[$action]) 
		 || !$core->auth->check('admin',$core->blog->id)){ return; }
		
		echo 
		'<h3>'.__('Confirm action').'</h3>'.
		'<form action="posts_actions.php" method="post">'.
		'<p>'.$allow[$action].'</p>'.
		'<p>'.$hidden_fields.$core->formNonce().
		form::hidden(array('action'),'confirmed'.$action).
		'<input type="submit" value="'.__('yes').'" /></p>'.
		'</form>';
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		$allow = array(
			'commentsopen','commentsclose','confirmedcommentsdelete',
			'trackbacksopen','trackbacksclose','confirmedtrackbacksdelete'
		);
		if (!in_array($action,$allow) 
		 || !$core->auth->check('admin',$core->blog->id)){ return; }
		
		try {
			while ($posts->fetch())
			{
				$id = $posts->post_id;
				
				switch ($action) {
					case 'commentsopen' :
					self::updPostOption($id,'post_open_comment',1);
					break;
					
					case 'commentsclose' :
					self::updPostOption($id,'post_open_comment',0);
					break;
					
					case 'trackbacksopen' :
					self::updPostOption($id,'post_open_tb',1);
					break;
					
					case 'trackbacksclose' :
					self::updPostOption($id,'post_open_tb',0);
					break;
					
					case 'confirmedcommentsdelete' :
					self::delPostComments($id,false);
					self::updPostOption($id,'nb_comment',0);
					break;
					
					case 'confirmedtrackbacksdelete' :
					self::delPostComments($id,true);
					self::updPostOption($id,'nb_trackback',0);
					break;
				}
			}
			if (!$core->error->flag()) {
				http::redirect($redir);
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	
	private static function updPostOption($id,$option,$value)
	{
		global $core;
		
		if (!$core->auth->check('admin',$core->blog->id)) {
			throw new Exception(__('You are not allowed to change this entry option'));
		}
		
		$id = abs((integer) $id);
		$cur = $core->con->openCursor($core->prefix.'post');
		
		$cur->{$option} = $value;
		$cur->post_upddt = date('Y-m-d H:i:s');
		
		$cur->update(
			'WHERE post_id = '.$id.' '.
			"AND blog_id = '".$core->con->escape($core->blog->id)."' "
		);
		$core->blog->triggerBlog();
	}
	
	private static function delPostComments($id,$tb=false)
	{
		global $core;

		$params = array(
			'no_content' => true,
			'post_id' => abs((integer) $id),
			'comment_trackback' => $tb ? 1: 0
		);
		$comments = $core->blog->getComments($params);
		
		while($comments->fetch())
		{
			// slower but preserve core behaviors
			$core->blog->delComment($comments->comment_id);
		}
	}
}
?>