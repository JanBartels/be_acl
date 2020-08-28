<?php
namespace JBartels\BeAcl\Controller;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * This class extends the permissions module in the TYPO3 Backend to provide
 * convenient methods of editing of page permissions (including page ownership
 * (user and group)) via new AjaxRequestHandler facility
 */
class PermissionAjaxController extends \TYPO3\CMS\Beuser\Controller\PermissionAjaxController
{

    /**
     * View object
     * @var view \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected $view;

    /**
     * Extension path
     * @var string
     */
    protected $extPath;

    /**
     * ACL table
     * @var string
     */
    protected $table = 'tx_beacl_acl';

    /**
     * Set the extension path
     * @param string $extPath
     */
    protected function setExtPath($extPath = null)
    {
        $this->extPath = empty($extPath) ? ExtensionManagementUtility::extPath('be_acl') : $extPath;
    }

    /**
     * Initialize the viewz
     */
    protected function initializeView()
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setPartialRootPaths(array('default' => $this->extPath . 'Resources/Private/Partials'));
        $this->view->assign('pageId', $this->conf['page']);
    }

    /**
     * The main dispatcher function. Collect data and prepare HTML output.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        // Actions handled by this class
        $handledActions = ['delete_acl'];
        $response = new HtmlResponse('');

        $requestBody = $request->getParsedBody();
        $action = $requestBody['action'] ?? null;
        $page = $requestBody['page'] ?? null;

        if ($page > 0 && in_array($action, $handledActions)) {
            return $this->handleAction($request, $response, $action);
        }

        return parent::dispatch($request);
    }

    protected function handleAction(ServerRequestInterface $request, ResponseInterface $response, $action)
    {
        $methodName = GeneralUtility::underscoredToLowerCamelCase($action);

        if (method_exists($this, $methodName)) {
            return call_user_func_array(array($this, $methodName), [$request, $response]);
        }

        $response->getBody()->write('Action method not found');

        return $response->withStatus(400);
    }

    protected function deleteAcl(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['LANG']->includeLLFile('EXT:be_acl/Resources/Private/Languages/locallang_perm.xlf');
        $GLOBALS['LANG']->getLL('aclUsers');

        $postData = $request->getParsedBody();
        $aclUid = !empty($postData['acl']) ? $postData['acl'] : null;

        if (!MathUtility::canBeInterpretedAsInteger($aclUid)) {
            return $this->errorResponse($response, $GLOBALS['LANG']->getLL('noAclId'), 400);
        }
        $aclUid = (int)$aclUid;
        // Prepare command map
        $cmdMap = [
            $this->table => [
                $aclUid => ['delete' => 1]
            ]
        ];

        try {
            // Process command map
            $tce = GeneralUtility::makeInstance(DataHandler::class);
            $tce->stripslashes_values = 0;
            $tce->start(array(), $cmdMap);
            $this->checkModifyAccess($this->table, $aclUid, $tce);
            $tce->process_cmdmap();
        } catch (\Exception $ex) {
            return $this->errorResponse($response, $ex->getMessage(), 403);
        }

        $body = [
            'title' => $GLOBALS['LANG']->getLL('aclSuccess'),
            'message' => $GLOBALS['LANG']->getLL('aclDeleted')
        ];
        // Return result
        $response->getBody()->write(json_encode($body));
        return $response;
    }

    protected function checkModifyAccess($table, $id, DataHandler $tcemainObj)
    {
        // Check modify access
        $modifyAccessList = $tcemainObj->checkModifyAccessList($table);
        // Check basic permissions and circumstances:
        if (!isset($GLOBALS['TCA'][$table]) || $tcemainObj->tableReadOnly($table) || !is_array($tcemainObj->cmdmap[$table]) || !$modifyAccessList) {
            throw new \JBartels\BeAcl\Exception\RuntimeException($GLOBALS['LANG']->getLL('noPermissionToModifyAcl'));
        }

        // Check table / id
        if (!$GLOBALS['TCA'][$table] || !$id) {
            throw new \JBartels\BeAcl\Exception\RuntimeException(sprintf($GLOBALS['LANG']->getLL('noEditAccessToAclRecord'), $id, $table));
        }

        // Check edit access
        $hasEditAccess = $tcemainObj->BE_USER->recordEditAccessInternals($table, $id, false, false, true);
        if (!$hasEditAccess) {
            throw new \JBartels\BeAcl\Exception\RuntimeException(sprintf($GLOBALS['LANG']->getLL('noEditAccessToAclRecord'), $id, $table));
        }
    }

    protected function errorResponse(ResponseInterface $response, $reason, $status = 500)
    {
        return $response->withStatus($status, $reason);
    }
}
