<?php
namespace WebExcess\Comments\ViewHelpers;

/*
 * This file is part of the WebExcess.Comments package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\ActionRequest;
use Neos\FluidAdaptor\Core\ViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;

class TranslateViewHelper extends ViewHelper\AbstractViewHelper
{

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param Translator $translator
     */
    public function injectTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Renders the translated label.

     * Replaces all placeholders with corresponding values if they exist in the
     * translated label.
     *
     * @param string $id Id to use for finding translation (trans-unit id in XLIFF)
     * @param string $value If $key is not specified or could not be resolved, this value is used. If this argument is not set, child nodes will be used to render the default
     * @param array $arguments Numerically indexed array of values to be inserted into placeholders
     * @param string $source Name of file with translations (use / as a directory separator)
     * @param string $package Target package key. If not set, the current package key will be used
     * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
     * @param string $locale An identifier of locale to use (NULL for use the default locale)
     * @return string Translated label or source label / ID key
     * @throws ViewHelperException
     */
    public function render($id = null, $value = null, array $arguments = array(), $source = null, $package = null, $quantity = null, $locale = null)
    {
        $localeObject = null;
        if ($locale !== null) {
            try {
                $localeObject = new Locale($locale);
            } catch (InvalidLocaleIdentifierException $e) {
                throw new ViewHelperException(sprintf('"%s" is not a valid locale identifier.', $locale), 1279815885);
            }
        }
        if ($source === null) {
            $source = $this->settings['translation']['source'];
        }
        if ($package === null) {
            $package = $this->settings['translation']['package'];
        }
        $originalLabel = $value === null ? $this->renderChildren() : $value;

        if ($id === null) {
            return (string)$this->translator->translateByOriginalLabel($originalLabel, $arguments, $quantity, $localeObject, $source, $package);
        }

        $translation = $this->translator->translateById($id, $arguments, $quantity, $localeObject, $source, $package);
        if ($translation !== null) {
            return (string)$translation;
        }
        if ($originalLabel !== null) {
            return $originalLabel;
        }
        return (string)$id;
    }
}
