<?php
namespace JBartels\BeAcl\View;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Todd Hossack (todd@tiraki.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @see \TYPO3\CMS\Backend\View\BackendTemplateView
 */
class BackendTemplateView extends \TYPO3\CMS\Backend\View\BackendTemplateView
{

    /**
     * Resolves the template root to be used inside other paths. Defaults to template view.
     * @see \TYPO3\CMS\Fluid\View\TemplateView::getTemplateRootPaths()
     *
     * @return array Path(s) to template root directory
     */
    public function getTemplateRootPaths()
    {
        return $this->templateView->getTemplateRootPaths();
    }

    /**
     * Set the root path(s) to the templates.
     * If set, overrides the one determined from $this->templateRootPathPattern
     * @see \TYPO3\CMS\Fluid\View\TemplateView::setTemplateRootPaths()
     *
     * @return void
     */
    public function setTemplateRootPaths(array $templateRootPaths)
    {
        $this->templateView->setTemplateRootPaths($templateRootPaths);
    }

    /**
     * Resolves the partial root to be used inside other paths.
     * @see \TYPO3\CMS\Fluid\View\TemplateView::getPartialRootPaths()
     *
     * @return array Path(s) to partial root directory
     */
    protected function getPartialRootPaths()
    {
        return $this->templateView->getPartialRootPaths();
    }

    /**
     * Set the root path(s) to the partials.
     * If set, overrides the one determined from $this->partialRootPathPattern
     * @see \TYPO3\CMS\Fluid\View\TemplateView::setPartialRootPaths()
     *
     * @return void
     */
    public function setPartialRootPaths(array $partialRootPaths)
    {
        $this->templateView->setPartialRootPaths($partialRootPaths);
    }

}
