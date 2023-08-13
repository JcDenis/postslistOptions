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
use dcCore;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Process;

/**
 * Improve admin class
 *
 * Add menu and dashboard icons, load Improve action modules.
 */
class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->addBehavior('adminPostsActions', function (ActionsPosts $pa) {
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
                        BackendBehaviors::{$pa->getAction()}($pa, $post);
                    }
                }
            );
        });

        return true;
    }
}
