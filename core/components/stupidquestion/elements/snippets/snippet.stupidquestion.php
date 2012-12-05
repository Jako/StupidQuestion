<?php
/*
 * StupidQuestion
 * 
 * Copyright 2010-2012 by Thomas Jakobi <thomas.jakobi@partout.info>
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
 * 
 * StupidQuestion snippet.
 */

// set base path
define(SQ_PATH, 'components/stupidquestion/');
define(SQ_BASE_PATH, MODX_CORE_PATH . SQ_PATH);

include SQ_BASE_PATH . 'model/stupidquestion/stupidquestion.class.php';

$answers = $modx->getOption('stupidQuestionAnswers', $scriptProperties, '');
$language = $modx->getOption('stupidQuestionLanguage', $scriptProperties, 'en');
$formcode = $modx->getOption('stupidQuestionFormcode', $scriptProperties, '');

// Init class
if (!isset($modx->stupidQuestion)) {
	$modx->stupidQuestion = new stupidQuestion($modx, $language, $formcode, $answers);
}

if (true) {
	$modx->stupidQuestion->output['htmlCode'] .= $modx->stupidQuestion->output['jsCode'];
} else {
	$modx->regClientScript($modx->stupidQuestion->output['jsCode']);
}
$modx->setPlaceholder('formit.stupidquestion_html', $modx->stupidQuestion->output['htmlCode']);

if (!$modx->stupidQuestion->checkAnswer()) {
	$modx->setPlaceholder('fi.error.' . $modx->stupidQuestion->answer['formfield'], '<span class="error">' . $modx->stupidQuestion->output['errorMessage'] . '</span>');
	return false;
}
return true;
?>
