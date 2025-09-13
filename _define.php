<?php
/**
 * @file
 * @brief       The plugin postslistOptions definition
 * @ingroup     postslistOptions
 *
 * @defgroup    postslistOptions Plugin dcLog.
 *
 * Add some options on admin posts list.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Posts list options',
    'Add some options on admin posts list',
    'Jean-Christian Denis and Contributors',
    '2025.09.13',
    [
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-05-14T18:23:22+00:00',
    ]
);
