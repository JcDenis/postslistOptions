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
declare(strict_types=1);

namespace Dotclear\Plugin\postslistOptions;

use ArrayObject;
use dcBlog;
use dcCore;
use dcPage;
use dcPostsActions;
use Dotclear\Helper\Html\Form\{
    Form,
    Hidden,
    Para,
    Submit,
    Text
};
use Dotclear\Helper\Html\Html;
use Exception;

class BackendBehaviors
{
    public static function commentsOpen(dcPostsActions $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_comment', 1);
        }
        dcPage::addSuccessNotice(__('Comments successfully opened.'));
        $pa->redirect(true);
    }

    public static function commentsClose(dcPostsActions $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_comment', 0);
        }
        dcPage::addSuccessNotice(__('Comments successfully closed.'));
        $pa->redirect(true);
    }

    public static function commentsDelete(dcPostsActions $pa, ArrayObject $post): void
    {
        $ids = self::getPostsIds($pa);

        if (empty($_POST['confirmdeletecomments'])) {
            $pa->beginPage(
                dcPage::breadcrumb([
                    Html::escapeHTML(dcCore::app()->blog->name) => '',
                    $pa->getCallerTitle()                       => $pa->getRedirection(true),
                    __('Delete posts comments')                 => '',
                ])
            );

            echo
            (new Form('plocd'))->method('post')->action($pa->getURI())->fields([
                (new Text('', $pa->getCheckboxes())),
                (new Text('p', __('Are you sure you want to delete all comments?'))),
                (new Para())->items(array_merge(
                    [
                        (new Submit(['do']))->value(__('yes')),
                        dcCore::app()->formNonce(false),
                        (new Hidden(['action'], 'commentsdelete')),
                        (new Hidden(['confirmdeletecomments'], '1')),
                    ],
                    $pa->hiddenFields(),
                )),
            ])->render();

            $pa->endPage();
        } else {
            foreach ($ids as $post_id) {
                self::delPostComments($post_id, false);
                self::updPostOption($post_id, 'nb_comment', 0);
            }
            dcPage::addSuccessNotice(__('Comments successfully deleted.'));
            $pa->redirect(true);
        }
    }

    public static function trackbacksOpen(dcPostsActions $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_tb', 1);
        }
        dcPage::addSuccessNotice(__('Trackbacks successfully opened.'));
        $pa->redirect(true);
    }

    public static function trackbacksClose(dcPostsActions $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_tb', 0);
        }
        dcPage::addSuccessNotice(__('Trackbacks successfully closed.'));
        $pa->redirect(true);
    }

    public static function trackbacksDelete(dcPostsActions $pa, ArrayObject $post): void
    {
        $ids = self::getPostsIds($pa);

        if (empty($_POST['confirmdeletetrackbacks'])) {
            $pa->beginPage(
                dcPage::breadcrumb([
                    Html::escapeHTML(dcCore::app()->blog->name) => '',
                    $pa->getCallerTitle()                       => $pa->getRedirection(true),
                    __('Delete posts trackbacks')               => '',
                ])
            );

            echo
            (new Form('plotd'))->method('post')->action($pa->getURI())->fields([
                (new Text('', $pa->getCheckboxes())),
                (new Text('p', __('Are you sure you want to delete all trackbacks?'))),
                (new Para())->items(array_merge(
                    [
                        (new Submit(['do']))->value(__('yes')),
                        dcCore::app()->formNonce(false),
                        (new Hidden(['action'], 'trackbacksdelete')),
                        (new Hidden(['confirmdeletetrackbacks'], '1')),
                    ],
                    $pa->hiddenFields(),
                )),
            ])->render();

            $pa->endPage();
        } else {
            foreach ($ids as $post_id) {
                self::delPostComments($post_id, true);
                self::updPostOption($post_id, 'nb_trackback', 0);
            }
            dcPage::addSuccessNotice(__('Trackbacks successfully deleted.'));
            $pa->redirect(true);
        }
    }

    private static function getPostsIds(dcPostsActions $pa): array
    {
        $posts_ids = $pa->getIDs();
        if (empty($posts_ids)) {
            throw new Exception(__('No entry selected'));
        }

        return $posts_ids;
    }

    private static function updPostOption(int $id, string $option, int $value): void
    {
        $id  = abs((int) $id);
        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::POST_TABLE_NAME);

        $cur->setField($option, $value);
        $cur->setField('post_upddt', date('Y-m-d H:i:s'));

        $cur->update(
            'WHERE post_id = ' . $id . ' ' .
            "AND blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' "
        );
        dcCore::app()->blog->triggerBlog();
    }

    private static function delPostComments(int $id, bool $tb = false): void
    {
        $params = [
            'no_content'        => true,
            'post_id'           => abs((int) $id),
            'comment_trackback' => $tb ? 1 : 0,
        ];
        $comments = dcCore::app()->blog->getComments($params);

        while ($comments->fetch()) {
            // slower but preserve behaviors
            dcCore::app()->blog->delComment($comments->f('comment_id'));
        }
    }
}
