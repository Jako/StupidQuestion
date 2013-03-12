<?php
/**
 * StupidQuestion - Userfriendly Captcha for MODX Revolution
 * 
 * StupidQuestion is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * StupidQuestion is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more 
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * StupidQuestion; if not, write to the Free Software Foundation, Inc., 
 * 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package stupidquestion
 * @subpackage snippetfile
 * @author Thomas Jakobi <thomas.jakobi@partout.info>
 * @copyright Copyright 2010-2013, Thomas Jakobi
 * @version 0.7.2
 *
 * StupidQuestion snippet.
 */
$corePath = $modx->getOption('stupidquestion.core_path', null, MODX_CORE_PATH . 'components/stupidquestion/');

$options = array();
$options['answers'] = $modx->getOption('stupidQuestionAnswers', $scriptProperties, '');
$options['language'] = $modx->getOption('stupidQuestionLanguage', $scriptProperties, 'en');
$options['formcode'] = $modx->getOption('stupidQuestionFormcode', $scriptProperties, '');
$options['scriptcode'] = $modx->getOption('stupidQuestionScriptcode', $scriptProperties, '');
$options['register'] = (boolean) $modx->getOption('stupidQuestionRegister', $scriptProperties, false);
$options['noscript'] = (boolean) $modx->getOption('stupidQuestionNoScript', $scriptProperties, false);

// Init class
include $corePath . 'model/stupidquestion/stupidquestion.class.php';
if (!isset($modx->stupidQuestion)) {
	$modx->stupidQuestion = new stupidQuestion($modx, $options);
}

if (!$options['noscript']) {
	if (!$options['register']) {
		$modx->stupidQuestion->output['htmlCode'] .= $modx->stupidQuestion->output['jsCode'];
	} else {
		$modx->regClientScript($modx->stupidQuestion->output['jsCode']);
	}
}
$modx->setPlaceholder('formit.stupidquestion_html', $modx->stupidQuestion->output['htmlCode']);

if (!$modx->stupidQuestion->checkAnswer()) {
	$hook->addError($modx->stupidQuestion->formfield, $modx->stupidQuestion->output['errorMessage']);
	return false;
}
return true;
?>
