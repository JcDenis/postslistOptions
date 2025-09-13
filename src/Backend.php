<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postslistOptions;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

/**
 * @brief       postslistOptions backend class.
 * @ingroup     postslistOptions
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehaviors([
            'adminPostsActions' => BackendBehaviors::adminPostsActions(...),
            'adminPagesActions' => BackendBehaviors::adminPostsActions(...),
        ]);

        return true;
    }
}
