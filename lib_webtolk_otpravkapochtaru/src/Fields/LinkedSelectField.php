<?php

/**
 * Base field for linked select controls that work with requestfield mappings and URL metadata.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       3.0.0
 */

namespace Webtolk\Otpravkapochtaru\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;

/**
 * Base field for linked Joomla select controls.
 *
 * @since  3.0.0
 */
abstract class LinkedSelectField extends ListField
{
    /**
     * Whether the fallback script tag has already been rendered.
     *
     * @since  3.0.0
     */
    private static bool $fallbackScriptRendered = false;

    /**
     * Use Joomla's native list layout; the field activates its own web asset during setup().
     *
     * @var    string
     * @since  3.0.0
     */
    protected $layout = 'joomla.form.field.list';

    /**
     * Prepare field metadata used by the linked-select layout and script.
     *
     * @param   \SimpleXMLElement  $element  The XML element object representation of the form field.
     * @param   mixed             $value    The form field value to validate.
     * @param   string|null       $group    The field name group control value.
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   3.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $isSetup = parent::setup($element, $value, $group);

        if ($isSetup) {
            $this->addLinkedSelectAttributes();
            $this->useLinkedSelectScript();
        }

        return $isSetup;
    }

    /**
     * Render the select and a guarded fallback script tag for forms where WebAssetManager misses field assets.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    protected function getInput(): string
    {
        return parent::getInput() . self::renderFallbackScriptTag();
    }

    /**
     * Activate the linked-select controller through Joomla WebAssetManager.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   3.0.0
     */
    private function useLinkedSelectScript(): void
    {
        $webAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
        $scriptName      = 'lib_wt_otpravkapochtaru.linked-select-fields';

        if (!$webAssetManager->assetExists('script', $scriptName)) {
            $webAssetManager->getRegistry()->addExtensionRegistryFile('lib_wt_otpravkapochtaru');
        }

        if ($webAssetManager->assetExists('script', $scriptName)) {
            $webAssetManager->useScript($scriptName);

            return;
        }

        $webAssetManager->registerAndUseScript(
            $scriptName,
            'lib_wt_otpravkapochtaru/js/linked-select-fields.js',
            [],
            ['defer' => true],
            ['core']
        );
    }

    /**
     * Render a single direct script tag as a fallback for plugin/edit form field rendering.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    private static function renderFallbackScriptTag(): string
    {
        if (self::$fallbackScriptRendered) {
            return '';
        }

        self::$fallbackScriptRendered = true;
        $src                          = rtrim(Uri::root(true), '/') . '/media/lib_wt_otpravkapochtaru/js/linked-select-fields.js';

        return '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" defer></script>';
    }

    /**
     * Add field metadata used by the linked-select JS controller.
     *
     * @return  void
     *
     * @since   3.0.0
     */
    private function addLinkedSelectAttributes(): void
    {
        $requestfields = $this->getRequestfieldsMap();
        $url           = trim((string) ($this->element['url'] ?? ''));

        if ($requestfields !== []) {
            $this->dataAttributes['data-wt-requestfields'] = json_encode(
                $requestfields,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) ?: '';
        }

        if ($url !== '') {
            $this->dataAttributes['data-wt-url'] = $url;
        }

        $cssClass  = trim((string) $this->class);
        $classes   = preg_split('/\\s+/', $cssClass, -1, PREG_SPLIT_NO_EMPTY);
        $classes[] = 'wt-linked-select-field';

        $this->class = trim(implode(' ', array_unique($classes)));
    }

    /**
     * Return selected form value for current field.
     *
     * @return string
     *
     * @since  3.0.0
     */
    protected function getCurrentValue(): string
    {
        if (is_array($this->value)) {
            return (string) ($this->value[0] ?? '');
        }

        if (is_scalar($this->value)) {
            return trim((string) $this->value);
        }

        return '';
    }

    /**
     * Return requestfields map where array keys are request params and values are form field names.
     *
     * @return array<string, string>
     *
     * @since  3.0.0
     */
    protected function getRequestfieldsMap(): array
    {
        $requestfields = [];
        $raw           = trim((string) ($this->element['requestfields'] ?? ''));

        if ($raw !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                foreach ($decoded as $requestField => $formField) {
                    if (!is_string($requestField) || !is_string($formField)) {
                        continue;
                    }

                    $requestField = trim($requestField);
                    $formField    = trim($formField);

                    if ($requestField === '' || $formField === '') {
                        continue;
                    }

                    $requestfields[$requestField] = $formField;
                }
            }
        }

        return $requestfields;
    }

    /**
     * Return form value for a request field key from map.
     *
     * @param   string  $requestField
     *
     * @return string
     *
     * @since  3.0.0
     */
    protected function getRequestFieldValue(string $requestField): string
    {
        $requestField = trim($requestField);

        if ($requestField === '') {
            return '';
        }

        $requestfields = $this->getRequestfieldsMap();

        if (!array_key_exists($requestField, $requestfields)) {
            return '';
        }

        $fieldName = $requestfields[$requestField];

        if (!($this->form instanceof Form)) {
            return '';
        }

        $fieldValue = $this->form->getValue($fieldName, '', null);

        if (is_array($fieldValue)) {
            return (string) ($fieldValue[0] ?? '');
        }

        if (!is_scalar($fieldValue)) {
            return '';
        }

        return trim((string) $fieldValue);
    }
}
