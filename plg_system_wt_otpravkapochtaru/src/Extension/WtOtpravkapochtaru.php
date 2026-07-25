<?php

/**
 * System plugin shell that provides configuration and language loading for the library package.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Plugin\System\WtOtpravkapochtaru\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

final class WtOtpravkapochtaru extends CMSPlugin
{
    /**
     * Ask Joomla to load plugin language files when the plugin is instantiated.
     *
     * @var    bool
     * @since  3.0.0
     */
    protected $autoloadLanguage = true;
}
