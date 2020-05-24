<?php

declare(strict_types=1);

namespace ChristianReifenscheid\Easylock\Middleware;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

/**
 * Class ContentProtector
 *
 * @package ChristianReifenscheid\Easylock\Middleware
 * @author  Christian Reifenscheid
 */
class ContentProtector implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * PageRepository
     *
     * @var \TYPO3\CMS\Core\Domain\Repository\PageRepository
     */
    protected $pageRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository */
        $this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);
    }

    /**
     * Replaces content of defined container with password form if not already filled out successfully
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        // get password of current page or rootline
        $password = $this->getPassword($GLOBALS['TSFE']->id);
        
        if ($password) {
            
            // check session for already entered password
            if ($GLOBALS['TSFE']->fe_user->getKey('ses', 'easylock')) {
                $sessionApproval = \ChristianReifenscheid\Easylock\Utility\ValidationUtility::validate($GLOBALS['TSFE']->fe_user->getKey('ses', 'easylock'), $password);
                
                // if session password is approved
                if ($sessionApproval) {
                    return $handler->handle($request);
                }
            }
            
            // else check request parameter
            $queryParams = $request->getQueryParams();
            
            if ($queryParams['easylock']) {
                
                $requestApproval = \ChristianReifenscheid\Easylock\Utility\ValidationUtility::validate($queryParams['easylock'], $password);
                
                // if entered password from request is approved
                if ($requestApproval) {
                    return $handler->handle($request);
                } else if ($requestApproval === false) {
                    // set flag to render error message due to incorrect entered password
                    $formValidationError = true;
                }
            }
            
            // get typoscript configuration
            $configuration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_easylock.']['view.'];
            
            // set template path
            $templateFile = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . $configuration['templateRootPath'] . $configuration['template']);
            
            // set up view
            $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
            $view->setFormat('html');
            $view->setTemplatePathAndFilename($templateFile);
            $view->assignMultiple([
                'formValidationError' => $formValidationError
            ]);

            // split content container
            $clearContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['clearContainer'], TRUE);

            // create dom object
            $dom = new \DOMDocument();

            // disable libxml errors
            libxml_use_internal_errors(true);

            // parse page content
            $dom->loadHTML($GLOBALS['TSFE']->content);

            // clear libxml error buffer
            libxml_clear_errors();

            // create xpath object
            $xpath = new \DOMXpath($dom);

            foreach ($clearContainer as $containerClass) {

                  // get dom nodes based on content containers
                  $nodes = $xpath->query('//div[contains(@class, \''.$containerClass.'\')]');

                  if ($nodes->length !== 0) {

                        // loop through all found containers
                        foreach($nodes as $node) {
                              // clear container
                              $node->nodeValue = '';
                        }
                  }
            }

            // get target container for password form
            $targetContentContainer = $configuration['targetContainer'];

            // get dom nodes based on content containers
            $targetNodes = $xpath->query('//div[contains(@class, \''.$targetContentContainer.'\')]');

            if ($targetNodes->length !== 0) {
                  // generate dom fragment
                  $formFragment = $dom->createDocumentFragment();
                  // append fluid template
                  $formFragment->appendXML($view->render());
                  // replace first target container node by dom fragment
                  $targetNodes[0]->parentNode->replaceChild($formFragment,$targetNodes[0]);
            }

            // write new content
            $GLOBALS['TSFE']->content = $dom->saveHTML();
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Get password from page properties
     *
     * @param int $pageId
     * @return null|string
     */
    protected function getPassword (int $pageId) : ?string {
        // only run, if page id is not 0
        if ($pageId !== 0) {

            // get page properties
            $page = $this->pageRepository->getPage($pageId);

            // check page for password
            if ($page['tx_easylock_password'] !== '') {
                // return password
                return $page['tx_easylock_password'];
            }

            else {
                // recursive call with parent page
                return $this->getPassword($page['pid']);
            }
        }

        return null;
    }
}