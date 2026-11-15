<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postslistOptions;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * @brief       postslistOptions backend behaviors class.
 * @ingroup     postslistOptions
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class BackendBehaviors
{
    public static function adminPostsActions(ActionsPosts $pa): void
    {
        $pa->addAction(
            [
                __('Comments') => [
                    __('Mark as opened')      => 'commentsopen',
                    __('Mark as closed')      => 'commentsclose',
                    __('Delete all comments') => 'commentsdelete',
                ],
                __('Trackbacks') => [
                    __('Mark as opened')        => 'trackbacksopen',
                    __('Mark as closed')        => 'trackbacksclose',
                    __('Delete all trackbacks') => 'trackbacksdelete',
                ],
            ],
            function (ActionsPosts $pa, ArrayObject $post) {
                $actions = [
                    'commentsopen',
                    'commentsclose',
                    'commentsdelete',
                    'trackbacksopen',
                    'trackbacksclose',
                    'trackbacksdelete',
                ];
                if (in_array($pa->getAction(), $actions)) {
                    self::{$pa->getAction()}($pa, $post);
                }
            }
        );
    }

    /**
     * @param   ArrayObject<string, mixed>      $post   The post
     */
    private static function commentsOpen(ActionsPosts $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_comment', 1);
        }
        Notices::addSuccessNotice(__('Comments successfully opened.'));
        $pa->redirect(true);
    }

    /**
     * @param   ArrayObject<string, mixed>      $post   The post
     */
    private static function commentsClose(ActionsPosts $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_comment', 0);
        }
        Notices::addSuccessNotice(__('Comments successfully closed.'));
        $pa->redirect(true);
    }

    /**
     * @param   ArrayObject<string, mixed>      $post   The post
     */
    private static function commentsDelete(ActionsPosts $pa, ArrayObject $post): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $ids = self::getPostsIds($pa);

        if (empty($_POST['confirmdeletecomments'])) {
            self::confirmDelete($pa, $ids, false);
        } else {
            foreach ($ids as $post_id) {
                self::delPostComments($post_id, false);
                self::updPostOption($post_id, 'nb_comment', 0);
            }
            Notices::addSuccessNotice(__('Comments successfully deleted.'));
            $pa->redirect(true);
        }
    }

    /**
     * @param   ArrayObject<string, mixed>      $post   The post
     */
    private static function trackbacksOpen(ActionsPosts $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_tb', 1);
        }
        Notices::addSuccessNotice(__('Trackbacks successfully opened.'));
        $pa->redirect(true);
    }

    /**
     * @param   ArrayObject<string, mixed>      $post   The post
     */
    private static function trackbacksClose(ActionsPosts $pa, ArrayObject $post): void
    {
        foreach (self::getPostsIds($pa) as $post_id) {
            self::updPostOption($post_id, 'post_open_tb', 0);
        }
        Notices::addSuccessNotice(__('Trackbacks successfully closed.'));
        $pa->redirect(true);
    }

    /**
     * @param   ArrayObject<string, mixed>      $post   The post
     */
    private static function trackbacksDelete(ActionsPosts $pa, ArrayObject $post): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $ids = self::getPostsIds($pa);

        if (empty($_POST['confirmdeletetrackbacks'])) {
            self::confirmDelete($pa, $ids, true);
        } else {
            foreach ($ids as $post_id) {
                self::delPostComments($post_id, true);
                self::updPostOption($post_id, 'nb_trackback', 0);
            }
            Notices::addSuccessNotice(__('Trackbacks successfully deleted.'));
            $pa->redirect(true);
        }
    }

    /**
     * Display deletion summary and confirmation form.
     *
     * @param   array<int>  $ids    The posts IDs
     */
    private static function confirmDelete(ActionsPosts $pa, array $ids, bool $tb): void
    {
        $rows  = [];
        $total = 0;
        $rs    = App::blog()->getPosts(['post_id' => $ids, 'no_content' => true, 'post_type' => '']);
        while ($rs->fetch()) {
            $count = (int) App::blog()->getComments([
                'post_id'           => $rs->intField('post_id'),
                'comment_trackback' => $tb ? 1 : 0,
            ], true)->f(0);
            $total += $count;
            $rows[] = (new Tr())->items([
                (new Td())->class('minimal')->items([
                    (new Checkbox(['entries[]'], true))->value($rs->intField('post_id')),
                ]),
                (new Td())->text(Html::escapeHTML($rs->strField('post_title'))),
                (new Td())->class(['nowrap', 'count'])->text((string) $count),
            ]);
        }

        $pa->beginPage(
            Page::breadcrumb([
                Html::escapeHTML(App::blog()->name()) => '',
                $pa->getCallerTitle()                 => $pa->getRedirection(true),
                $tb ? __('Delete posts trackbacks') : __('Delete posts comments') => '',
            ])
        );

        echo (new Form($tb ? 'plotd' : 'plocd'))
            ->method('post')
            ->action($pa->getURI())
            ->fields([
                (new Table())->class('posts-list')->items([
                    (new Thead())->items([
                        (new Tr())->items([
                            (new Th())->text(''),
                            (new Th())->text(__('Title')),
                            (new Th())->class('nowrap')->text($tb ? __('Trackbacks') : __('Comments')),
                        ]),
                    ]),
                    (new Tbody())->items($rows),
                ]),
                (new Para())->class('warning')->items([
                    (new Text(null, sprintf(
                        $tb ?
                            __('One trackback, whatever its status, will be permanently deleted.', '%d trackbacks, whatever their status, will be permanently deleted.', $total) :
                            __('One comment, whatever its status, will be permanently deleted.', '%d comments, whatever their status, will be permanently deleted.', $total),
                        $total
                    ))),
                ]),
                (new Para())->items([
                    (new Checkbox($tb ? 'confirmdeletetrackbacks' : 'confirmdeletecomments'))
                        ->value('1')
                        ->required(true)
                        ->label(new Label(__('I confirm this deletion'), Label::IL_FT)),
                ]),
                (new Para())->items([
                    (new Submit(['do']))->value(__('Delete')),
                    App::nonce()->formNonce(),
                    (new Hidden(['action'], $tb ? 'trackbacksdelete' : 'commentsdelete')),
                    ... $pa->hiddenFields(),
                ]),
            ])
            ->render();

        $pa->endPage();
    }

    /**
     * @return  array<int>   The list
     */
    private static function getPostsIds(ActionsPosts $pa): array
    {
        $posts_ids = array_map(fn ($v): int => (int) $v, $pa->getIDs());
        if (empty($posts_ids)) {
            throw new Exception(__('No entry selected'));
        }

        return $posts_ids;
    }

    private static function updPostOption(int $id, string $option, int $value): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $id  = abs((int) $id);
        $cur = App::blog()->openPostCursor();

        $cur->setField($option, $value);
        $cur->setField('post_upddt', date('Y-m-d H:i:s'));

        $cur->update(
            'WHERE post_id = ' . $id . ' ' .
            "AND blog_id = '" . App::db()->con()->escapeStr(App::blog()->id()) . "' "
        );
        App::blog()->triggerBlog();
    }

    private static function delPostComments(int $id, bool $tb = false): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $params = [
            'no_content'        => true,
            'post_id'           => abs((int) $id),
            'comment_trackback' => $tb ? 1 : 0,
        ];
        $comments = App::blog()->getComments($params);

        while ($comments->fetch()) {
            // slower but preserve behaviors
            App::blog()->delComment($comments->intField('comment_id'));
        }
    }
}
