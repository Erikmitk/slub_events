<?php
namespace Slub\SlubEvents\ViewHelpers\Form;
/***************************************************************
*  Copyright notice
*
*  (c) 2016 Alexander Bigga <alexander.bigga@slub-dresden.de>, SLUB Dresden
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class RteViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
/**
 * RTE view helper.
 * The value of the text area needs to be set via the "value" attribute, as with all other form ViewHelpers.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.rte name="myRteTextArea" rows="5" cols="40" value="This is shown inside the rte textarea" />
 * <f:form.rte property="content" rows="5" cols="40" /><br />
 * </code>
 *
 * Output:
 * The form output is wrapped with some script tags including javascript from rte (tx_rtehtmlarea)
 * and is modified: Among other things the onsubmit atrribute of the form is set or modified.
 * <textarea id="RTEareatx_myext_pi1[myRteTextArea]_1" name="tx_myext_pi1[myRteTextArea]" style="position:relative; left:0px; top:0px; height:380px; width:460px; border: 1px solid black;">This is shown inside the textarea</textarea>
 *
 * @package Fluid
 * @subpackage ViewHelpers\Form
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * The width of the RTE will be expanded, if $docLarge is set to TRUE
     *
     * @var boolean
     */
    public $docLarge = FALSE;

    /**
     * Counter for RTE
     *
     * @var int
     */
    public $RTEcounter;

    /**
     * Form name
     *
     * @var string
     */
    public $formName;

    /**
     * Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
     *
     * @var string
     */
    public $additionalJS_initial = '';

    /**
     * Additional JavaScript to be printed before the form
     *
     * @var array
     */
    public $additionalJS_pre = array();

    /**
     * Additional JavaScript to be printed after the form
     *
     * @var array
     */
    public $additionalJS_post = array();

    /**
     * Additional JavaScript to be executed on submit
     *
     * @var array
     */
    public $additionalJS_submit = array();

    /**
     * Additional JavaScript to be printed before the form
     *
     * @var string
     */
    protected $additionalJS_pre_complete;

    /**
     * Additional JavaScript to be printed after the form
     *
     * @var string
     */
    protected $additionalJS_post_complete;

    /**
     * The completed JavaScript to be executed on submit
     *
     * @var string
     */
    protected $additionalJS_submit_complete;

    /**
     * Array of standard content for rendering form fields from TCEforms
     *
     * @var array
     */
    public $PA = array();

    /**
     * "special" configuration - what is found at position 4 in the types configuration of a field from record, parsed into an array.
     *
     * @var array
     */
    public $specConf = array();

    /**
     * Configuration for RTEs; A mix between TSconfig and otherwise.
     * Contains configuration for display, which buttons are enabled, additional transformation information etc.
     *
     * @var array
     */
    public $thisConfig = array();

    /**
     * Constructor. Used to create an instance of \TYPO3\CMS\Rtehtmlarea\RteHtmlAreaBase used by the render() method.
     * @return void
     */
    public function __construct() {
//        $this->RTEObj = GeneralUtility::makeInstance('\\Slub\\SlubEvents\\Helper\\RteBackendController');
        $this->RTEObj = GeneralUtility::makeInstance('\\TYPO3\\CMS\\Rtehtmlarea\\RteHtmlAreaBase');
        $this->tceforms = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Form\\FormEngine');
        $this->tceforms->initDefaultBEMode();
    }

    /**
     * @var string
     */
    protected $tagName = 'textarea';

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerTagAttribute('autofocus', 'string', 'Specifies that a text area should automatically get focus when the page loads');
        $this->registerTagAttribute('rows', 'int', 'The number of rows of a text area');
        $this->registerTagAttribute('cols', 'int', 'The number of columns of a text area');
        $this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerTagAttribute('placeholder', 'string', 'The placeholder of the textarea');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the rte htmlarea.
     *
     * @return string
     * @author Frank Frewer <info@frankfrewer.de>
     * @api
     */
    public function render() {

//        $frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
//        $this->storagePid = $frameworkConfiguration['persistence']['storagePid'];
//
        $name = $this->getName();
        $property = $this->arguments['property'];

        $this->registerFieldNameForFormTokenGeneration($name);

        $this->tag->forceClosingTag(TRUE);
        $this->tag->addAttribute('name', $name);
        $this->tag->setContent($this->getValue());

        $this->setErrorClassAttribute();

        $value = $this->renderChildren();

        // RTE
        $RTEtypeVal = 'text';
        $this->RTEcounter++;

        $this->PA['itemFormElName'] = $name;
        $this->PA['itemFormElValue'] = $this->getValue();
        $thePidValue = $GLOBALS['TSFE']->id;
        $RTEItem = $this->RTEObj->drawRTE($this->tceforms,'',$property,$row=array(), $this->PA, $this->specConf, $this->thisConfig, $RTEtypeVal, '', $thePidValue);

        $this->additionalJS_pre_complete = $this->additionalJS_initial.'
		<script type="text/javascript">'. implode(chr(10), $this->additionalJS_pre) . "
		</script>\n";

        $this->additionalJS_post_complete = '
			<script type="text/javascript">'. implode(chr(10), $this->additionalJS_post) . "
			</script>\n";

        $placeHolderElementName = substr_replace($name, '_TRANSFORM_', strlen($name)-strlen($property)-1, 0);

        $submitPost .= "
		var transformField=document.getElementsByName('" . $placeHolderElementName . "').item(0);
		if (transformField) {
			transformField.parentNode.removeChild(transformField);
		}";

        $this->additionalJS_submit_complete = implode(';', $this->additionalJS_submit) . $submitPost;

        $this->addRteJsToViewHelperVariableContainer();
        $content = $RTEItem;
        return $content;

    }
    /**
     * Adds the JavaScript that is bound to this rte field to the ViewHelperVariableContainer.
     *
     * @return void
     */
    protected function addRteJsToViewHelperVariableContainer() {
        if ($this->viewHelperVariableContainer->exists('RteViewHelper', 'rte')) {
            $rte = $this->viewHelperVariableContainer->get('RteViewHelper', 'rte');
        } else {
            $rte = array();
        }
        $rte[] = array('pre' => $this->additionalJS_pre_complete,
            'post' => $this->additionalJS_post_complete,
            'submit' => $this->additionalJS_submit_complete,
        );
        $this->viewHelperVariableContainer->addOrUpdate('RteViewHelper', 'rte', $rte);
    }
}

?>
