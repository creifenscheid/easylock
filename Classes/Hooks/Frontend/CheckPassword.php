<?php
namespace SPL\SplEasylock\Hooks\Frontend;

use Doctrine\Common\Util\Debug;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class EasyLock
{
    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var integer
     */
    protected $currentPage;

    /**
     * @var array
     */
    protected $configuration;

    public function __construct (){
        // init page repository
        $this->pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
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
        if($this->currentPage['tx_spleasylock_password'] != '') {
            // based on current page
            $this->password = $this->currentPage['tx_spleasylock_password'];
        }

        // check parent pages recursively
        else {
            // recursive
            $this->getPasswordRecursive($this->currentPage['pid']);
        }

        // validate password if set
        if ($this->password != '') {
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
                    $this->configuration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_spleasylock.']['view.'];

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

                    // flag: form inserted y/n
                    $formInserted = FALSE;

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


    protected function validateSession () {

        // get session parameter
        $sessionPageProtector = $GLOBALS['TSFE']->fe_user->getKey('ses','spleasylock');

        // check session for password
        if ($sessionPageProtector != '') {

            // validate session value against set password
            $validation = $this->validatePassword($sessionPageProtector);

            if($validation === 1) {
                return 1;
            }

            else {
                return 0;
            }
        }

        else {
            // password not set in session
            return 0;
        }
    }

    /**
     * check post vars if password form has been sent
     *
     * @return mixed
     */
    protected function validatePostVars () {

        // get get/post value of easylock
        $gpEasylock = GeneralUtility::_GP('spleasylock');

        // easylock set
        if ($gpEasylock != '') {

            // validate value against set password
            $validation = $this->validatePassword(md5($gpEasylock));

            if($validation === 1) {
                // set session parameter
                $GLOBALS['TSFE']->fe_user->setKey('ses', 'spleasylock', $this->password);
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
    protected function validatePassword($notValidatedPassword) {

        // validate password
        if ($this->password == $notValidatedPassword) {
            return 1;
        }

        else {
            return 0;
        }
    }

    /**
     * get password from page properties recursive
     *
     * @param $parentPageId
     */
    protected function getPasswordRecursive($parentPageId) {
        // only run function, when parent pid is not 0
        if ($parentPageId != 0) {

            // get page properties of parent page
            $parentPage = $this->pageRepository->getPage($parentPageId);

            // check parent page for protector password
            if ($parentPage['tx_spleasylock_password'] != '') {
                // set password
                $this->protectorPassword = $parentPage['tx_spleasylock_password'];
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

?>