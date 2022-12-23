<?php
/**
 * @brief postslistOptions, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and Contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id)) {
    return null;
}

dcCore::app()->addBehavior('adminPostsActions', function (dcPostsActions $pa) {
    $pa->addAction(
        [
            __('Comments') => [
                __('Mark as opened') => 'commentsopen',
                __('Mark as closed') => 'commentsclose',
                __('Delete all comments') => 'commentsdelete',
            ],
            __('Trackbacks') => [
                __('Mark as opened') => 'trackbacksopen',
                __('Mark as closed') => 'trackbacksclose',
                __('Delete all trackbacks') => 'trackbacksdelete',
            ],
        ],
        function (dcPostsActions $pa, ArrayObject $post) {
            $actions = [
                'commentsopen',
                'commentsclose',
                'commentsdelete',
                'trackbacksopen',
                'trackbacksclose',
                'trackbacksdelete',
            ];
            if (in_array($pa->getAction(), $actions)) {
                behaviorsPostlistOptions::{$pa->getAction()}($pa, $post);
            }
        }
    );
});

class behaviorsPostlistOptions
{
    public static function commentsOpen(dcPostsActions $pa, ArrayObject $post)
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_comment', 1);
        }
        dcAdminNotices::addSuccessNotice(__('Comments successfully opened.'));
        $pa->redirect(true);
    }

    public static function commentsClose(dcPostsActions $pa, ArrayObject $post)
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_comment', 0);
        }
        dcAdminNotices::addSuccessNotice(__('Comments successfully closed.'));
        $pa->redirect(true);
    }

    public static function commentsDelete(dcPostsActions $pa, ArrayObject $post)
    {
        $ids = self::getPostsIds($pa);

        if (empty($_POST['confirmdeletecomments'])) {
            $pa->beginPage(
                dcPage::breadcrumb([
                    html::escapeHTML(dcCore::app()->blog->name) => '',
                    $pa->getCallerTitle()                       => $pa->getRedirection(true),
                    __('Delete posts comments')                 => '',
                ])
            );

            echo
            '<form action="' . $pa->getURI() . '" method="post">' .
            $pa->getCheckboxes() .
            '<p>' . __('Are you sure you want to delete all comments?') . '</p>' .
            '<p>' .
            dcCore::app()->formNonce() .
            $pa->getHiddenFields() .
            form::hidden(['action'], 'commentsdelete') .
            form::hidden(['confirmdeletecomments'], 1) .
            '<input type="submit" value="' . __('yes') . '" /></p>' .
            '</form>';

            $pa->endPage();
        } else {
            foreach ($ids as $post_id) {
                self::delPostComments($post_id, false);
                self::updPostOption($post_id, 'nb_comment', 0);
            }
            dcAdminNotices::addSuccessNotice(__('Comments successfully deleted.'));
            $pa->redirect(true);
        }
    }

    public static function trackbacksOpen(dcPostsActions $pa, ArrayObject $post)
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_tb', 1);
        }
        dcAdminNotices::addSuccessNotice(__('Trackbacks successfully opened.'));
        $pa->redirect(true);
    }

    public static function trackbacksClose(dcPostsActions $pa, ArrayObject $post)
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_tb', 0);
        }
        dcAdminNotices::addSuccessNotice(__('Trackbacks successfully closed.'));
        $pa->redirect(true);
    }

    public static function trackbacksDelete(dcPostsActions $pa, ArrayObject $post)
    {
        $ids = self::getPostsIds($pa);

        if (empty($_POST['confirmdeletetrackbacks'])) {
            $pa->beginPage(
                dcPage::breadcrumb([
                    html::escapeHTML(dcCore::app()->blog->name) => '',
                    $pa->getCallerTitle()                       => $pa->getRedirection(true),
                    __('Delete posts trackbacks')               => '',
                ])
            );

            echo
            '<form action="' . $pa->getURI() . '" method="post">' .
            $pa->getCheckboxes() .
            '<p>' . __('Are you sure you want to delete all trackbacks?') . '</p>' .
            '<p>' .
            dcCore::app()->formNonce() .
            $pa->getHiddenFields() .
            form::hidden(['action'], 'trackbacksdelete') .
            form::hidden(['confirmdeletetrackbacks'], 1) .
            '<input type="submit" value="' . __('yes') . '" /></p>' .
            '</form>';

            $pa->endPage();
        } else {
            foreach ($ids as $post_id) {
                self::delPostComments($post_id, true);
                self::updPostOption($post_id, 'nb_trackback', 0);
            }
            dcAdminNotices::addSuccessNotice(__('Trackbacks successfully deleted.'));
            $pa->redirect(true);
        }
    }

    private static function getPostsIds(dcPostsActions $pa)
    {
        $posts_ids = $pa->getIDs();
        if (empty($posts_ids)) {
            throw new Exception(__('No entry selected'));
        }

        return $posts_ids;
    }

    private static function updPostOption($id, $option, $value)
    {
        $id  = abs((int) $id);
        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::POST_TABLE_NAME);

        $cur->{$option}  = $value;
        $cur->post_upddt = date('Y-m-d H:i:s');

        $cur->update(
            'WHERE post_id = ' . $id . ' ' .
            "AND blog_id = '" . dcCore::app()->con->escape(dcCore::app()->blog->id) . "' "
        );
        dcCore::app()->blog->triggerBlog();
    }

    private static function delPostComments($id, $tb = false)
    {
        $params = [
            'no_content'        => true,
            'post_id'           => abs((int) $id),
            'comment_trackback' => $tb ? 1 : 0,
        ];
        $comments = dcCore::app()->blog->getComments($params);

        while ($comments->fetch()) {
            // slower but preserve behaviors
            dcCore::app()->blog->delComment($comments->__get('comment_id'));
        }
    }
}
