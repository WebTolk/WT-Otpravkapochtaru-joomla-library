<?php

/**
 * Joomla note field that renders WebTolk plugin information in the plugin edit form.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Plugin\System\WtOtpravkapochtaru\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Language\Text;

class PlugininfoField extends NoteField
{
    /**
     * Field type used by Joomla when resolving XML field definitions.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Plugininfo';

    /**
     * Render the WebTolk info block with plugin version and translated manifest description.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     *
     * @throws  \Exception
     */
    protected function getInput(): string
    {
        $data    = $this->form->getData();
        $element = (string) $data->get('element');
        $folder  = (string) $data->get('folder');

        $app = Factory::getApplication();
        $extension = 'plg_' . $folder . '_' . $element;
        $app->getLanguage()->load($extension . '.sys', JPATH_ADMINISTRATOR)
            || $app->getLanguage()->load($extension . '.sys', JPATH_SITE);

        $doc = $app->getDocument();
        $wa  = $doc->getWebAssetManager();
        $wa->addInlineStyle("
			.plugin-info-img-svg:hover * {
				cursor:pointer;
			}
		");

        $manifestPath = JPATH_SITE . '/plugins/' . $folder . '/' . $element . '/' . $element . '.xml';
        $pluginInfo   = is_file($manifestPath) ? simplexml_load_file($manifestPath) : false;

        $version        = $pluginInfo instanceof \SimpleXMLElement ? (string) $pluginInfo->version : '';
        $descriptionKey = $pluginInfo instanceof \SimpleXMLElement ? (string) $pluginInfo->description : '';
        $description    = Text::_($descriptionKey ?: 'PLG_SYSTEM_WTOTPRAVKAPOCHTARU_DESC');

        return '<div class="d-flex shadow p-4">
			<div class="flex-shrink-0">
				<a href="https://web-tolk.ru" target="_blank" rel="noopener noreferrer">
					<svg class="plugin-info-img-svg" width="200" height="50" xmlns="http://www.w3.org/2000/svg">
						<g>
							<title>Go to https://web-tolk.ru</title>
							<text font-weight="bold" xml:space="preserve" text-anchor="start"
							      font-family="Helvetica, Arial, sans-serif" font-size="32" id="svg_3" y="36.085949"
							      x="8.152073" stroke-opacity="null" stroke-width="0" stroke="#000"
							      fill="#0fa2e6">Web</text>
							<text font-weight="bold" xml:space="preserve" text-anchor="start"
							      font-family="Helvetica, Arial, sans-serif" font-size="32" id="svg_4" y="36.081862"
							      x="74.239105" stroke-opacity="null" stroke-width="0" stroke="#000"
							      fill="#384148">Tolk</text>
						</g>
					</svg>
				</a>
			</div>
			<div class="flex-grow-1 ms-3">
				<span class="badge bg-success text-white">v.' . htmlspecialchars($version, ENT_QUOTES, 'UTF-8') . '</span>
				' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '
			</div>
		</div>';
    }

    /**
     * Hide the regular label because the field renders a full informational block.
     *
     * @return  string  The field label markup.
     *
     * @since   1.7.0
     */
    protected function getLabel(): string
    {
        return '';
    }

    /**
     * Return the hidden label as field title for Joomla form compatibility.
     *
     * @return  string  The field title.
     *
     * @since   1.7.0
     */
    protected function getTitle(): string
    {
        return $this->getLabel();
    }
}
