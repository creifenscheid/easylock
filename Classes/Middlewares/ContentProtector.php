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
     * password set in current page or a rootline page
     *
     * @var string
     */
    protected $password;

    /**
     * Page id if current page
     *
     * @var integer
     */
    protected $currentPage;

    /**
     * typoscript configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * Replaces content of defined container with password form if not already filled out successfully
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        /** @var \TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository */
        $this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);
        
        return $handler->handle($request);
    }
    
    /**
     * user_checkPassword
     *
     * @param $params
     * @param $obj
     */
    public function checkPassword (&$params, &$obj) {

        // get current page
        $this->currentPage = $params['pObj']->page;

        // check current password if easylock is set
        if($this->currentPage['tx_easylock_password'] !== '') {
            // based on current page
            $this->password = $this->currentPage['tx_easylock_password'];
        }

        // check parent pages recursively
        else {
            // recursive
            $this->getPasswordRecursive($this->currentPage['pid']);
        }

        // validate password if set
        if ($this->password !== '') {

            // validate session
            $validateSession = $this->validateSession();

            // if session validation fails
            if(!$validateSession) {

                // validate post vars
                $validatePost = $this->validatePostVars();

                /*
                 * 0: false
                 * 1: true
                 * 2: password incorrect
                 */
                // if post vars validation fails
                if ($validatePost == 0 || $validatePost == 2) {

                    // init view
                    $view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);

                    // set format
                    $view->setFormat('html');

                    // password failed
                    if ($validatePost == 2) {

                        $assignedData = array(
                            'passwordfail'  => 'true'
                        );

                        $view->assignMultiple($assignedData);
                    }

                    // get typoscript configuration
                    $this->configuration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_easylock.']['view.'];

                    // set template path
                    $templateFile = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath(PATH_site . $this->configuration['templateRootPath'] . $this->configuration['template']);

                    // get container which shall be cleared
                    $clearContainer = $this->configuration['clearContainer'];

                    // split content container
                    $clearContainer = GeneralUtility::trimExplode(',', $clearContainer, TRUE);

                    // set view template
                    $view->setTemplatePathAndFilename($templateFile);

                    // create dom object
                    $dom = new \DOMDocument();

                    // disable libxml errors
                    libxml_use_internal_errors(true);

                    // parse page content
                    $dom->loadHTML($params['pObj']->content);

                    // clear libxml error buffer
                    libxml_clear_errors();

                    // create xpath object
                    $xpath = new \DOMXpath($dom);

                    foreach ($clearContainer as $containerClass) {

                        // get dom nodes based on content containers
                        $nodes = $xpath->query('//div[contains(@class, \''.$containerClass.'\')]');

                        if ($nodes->length != 0) {

                            // loop through all found containers
                            foreach($nodes as $node) {
                                // clear container
                                $node->nodeValue = '';
                            }
                        }
                    }

                    // get target container for password form
                    $targetContentContainer = $this->configuration['targetContainer'];

                    // get dom nodes based on content containers
                    $targetNodes = $xpath->query('//div[contains(@class, \''.$targetContentContainer.'\')]');

                    if ($targetNodes->length != 0) {
                        // generate dom fragment
                        $formFragment = $dom->createDocumentFragment();
                        // append fluid template
                        $formFragment->appendXML($view->render());
                        // replace first target container node by dom fragment
                        $targetNodes[0]->parentNode->replaceChild($formFragment,$targetNodes[0]);
                    }

                    // set new content
                    $params['pObj']->content = $dom->saveHTML();

                    // render password form
                    return;
                }

                // post var validation succeeded - render content
                else {
                    return;
                }
            }

            // session validation succeeded - render content
            else {
                return;
            }
        }

        // no password set - render content
        else {
            return;
        }
    }
    
    /**
     * Check session if correct password has already been entered
     *
     * @return bool
     */
    protected function validateSession () : bool
    {
        // get session parameter
        $sessionPageProtector = $GLOBALS['TSFE']->fe_user->getKey('ses','easylock');

        // check session for password
        if ($sessionPageProtector !== '') {

            // validate session value against entered password
            $validation = $this->validatePassword($sessionPageProtector);

            if($validation === 1) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Check post vars if password form has been sent
     *
     * @return int
     */
    protected function validatePostVars () : int
    {

        // get get/post value of easylock
        $gpEasylock = GeneralUtility::_GP('easylock');

        // easylock set
        if ($gpEasylock != '') {

            // validate value against set password
            $validation = $this->validatePassword($gpEasylock, TRUE);

            if($validation === 1) {
                // set session parameter
                $GLOBALS['TSFE']->fe_user->setKey('ses', 'easylock', $this->password);
                $GLOBALS['TSFE']->storeSessionData();

                return 1;
            }

            else {
                return 2;
            }
        }

        // easylock not set in post vars
        else {
            return 0;
        }
    }
    
    /**
     * validate the user password (session data or form input)
     *
     * @param $notValidatedPassword
     * @return bool
     */
    protected function validatePassword($notValidatedPassword, $saltedMode = FALSE) : bool {

        if ($saltedMode) {
            $success = FALSE;

            // check salting utility
            if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled ('BE')) {

                // if enable for be - get instance of salt factory object
                $saltObj = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance ();

                // check salt factory objects
                if (is_object($saltObj)) {
                    $success = $saltObj->checkPassword($notValidatedPassword, $this->password);
                }
            }

            if ($success) {
                return 1;
            }

            else {
                return 0;
            }
        }

        else {
            // validate password
            if ($this->password === $notValidatedPassword) {
                return 1;
            }

            else {
                return 0;
            }
        }
    }

    /**
     * get password from page properties recursive
     *
     * @param $parentPageId
     * @return void
     */
    protected function getPasswordRecursive($parentPageId) : void {
        // only run function, when parent pid is not 0
        if ($parentPageId != 0) {

            // get page properties of parent page
            $parentPage = $this->pageRepository->getPage($parentPageId);

            // check parent page for protector password
            if ($parentPage['tx_easylock_password'] != '') {
                // set password
                $this->password = $parentPage['tx_easylock_password'];
                return;
            }

            else {
                // recursive call with grandparent page
                $this->getPasswordRecursive($parentPage['pid']);
            }
        }

        else {
            return;
        }
    }
}