<?php
namespace JBartels\BeAcl\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Get a value from an array by given key.
 */
class ArrayElementViewHelper extends \TYPO3\CMS\Beuser\ViewHelpers\ArrayElementViewHelper implements CompilableInterface
{

    /**
     * Return array element by key.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @throws \TYPO3\CMS\Beuser\Exception
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $array = $arguments['array'];
        $key = $arguments['key'];
        $subKey = $arguments['subKey'];
        $result = '';

        if (is_array($array)) {
            $result = static::getValue($array, $key);
            if (is_array($result) && $subKey) {
                $result = static::getValue($result, $subKey);
            }
        }

        if (!is_scalar($result) && !is_null($result)) {
            throw new \TYPO3\CMS\Beuser\Exception(
                'Only scalar or null return values (string, int, float or double, null) are supported.',
                1382284105
            );
        }
        return $result;
    }

    protected static function getValue($array, $key, $del = '.', $default = null)
    {
        try {
            $result = ArrayUtility::getValueByPath($array, (string)$key, '.');
        } catch (\RuntimeException $ex) {
            $result = $default;
        }
        return $result;
    }
}
