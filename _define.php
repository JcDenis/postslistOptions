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
if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'Posts list options',
    'Add some options on admin posts list',
    'Jean-Christian Denis and Contributors',
    '2022.11.28',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcAuth::PERMISSION_ADMIN,
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/postslistOptions',
        'details'     => 'https://plugins.dotaddict.org/dc2/details/postslistOptions',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/postslistOptions/master/dcstore.xml',
    ]
);
