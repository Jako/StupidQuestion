<?php
/**
 * StupidQuestion Hook
 *
 * @package stupidquestion
 * @subpackage hook
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var fiHooks $hook
 */

$corePath = $modx->getOption('stupidquestion.core_path', null, $modx->getOption('core_path') . 'components/stupidquestion/');
/** @var StupidQuestion $stupidquestion */
$stupidquestion = $modx->getService('stupidquestion', 'StupidQuestion', $corePath . 'model/stupidquestion/', array(
    'core_path' => $corePath,
    'language' => $modx->getOption('stupidQuestionLanguage', $scriptProperties,  $modx->getOption('cultureKey'), true),
    'formcode' => $modx->getOption('stupidQuestionFormcode', $scriptProperties, '',true),
    'jscode' => $modx->getOption('stupidQuestionScriptcode', $scriptProperties, '', true)
));

$register = (boolean)$modx->getOption('stupidQuestionRegister', $scriptProperties, false, true);
$noscript = (boolean)$modx->getOption('stupidQuestionNoScript', $scriptProperties, false, true);

if (!$noscript) {
    if (!$register) {
        $stupidquestion->output['htmlCode'] .= $stupidquestion->output['jsCode'];
        $stupidquestion->output['jsCode'] = '';
    } else {
        $modx->regClientScript($stupidquestion->output['jsCode']);
    }
}
$modx->setPlaceholder('formit.stupidquestion_html', $stupidquestion->output['htmlCode']);

if (!$stupidquestion->checkAnswer()) {
    $hook->addError($stupidquestion->formfield, $stupidquestion->output['errorMessage']);
    return false;
}
return true;
