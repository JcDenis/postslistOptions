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
use dcAuth;
use dcCore;
use dcNsProcess;
use dcPostsActions;

/**
 * Improve admin class
 *
 * Add menu and dashboard icons, load Improve action modules.
 */
class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN') && dcCore::app()->auth->check(
            dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]),
            dcCore::app()->blog->id
        );

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->addBehavior('adminPostsActions', function (dcPostsActions $pa) {
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
                        BackendBehaviors::{$pa->getAction()}($pa, $post);
                    }
                }
            );
        });

        return true;
    }
}
