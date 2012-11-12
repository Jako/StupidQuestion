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
 * StupidQuestion modX service class.
 */

if (!class_exists('JavaScriptPacker')) {
	include SQ_BASE_PATH . 'model/includes/class.JavaScriptPacker.php';
}
include SQ_BASE_PATH . 'model/includes/include.parsetpl.php';

if (!class_exists('stupidQuestion')) {

	class stupidQuestion {

		public $output = array();
		public $answer = array();
		private $modx;
		private $settings = array();
		private $templates = array();

		function __construct($modx, $language = 'en', $formcode = '', $answer = '') {
			$this->modx = & $modx;
			$this->prepareSettings($language, $answer);
			$this->prepareTemplates($formcode);
			$this->setQuestion();
		}

		// Return the include path of a configuration/template/whatever file
		function includeFile($name, $type = 'config', $extension = '.inc.php') {

			$folder = (substr($type, -1) != 'y') ? $type . 's/' : substr($folder, 0, -1) . 'ies/';
			$allowedConfigs = glob(SQ_BASE_PATH . $folder . '*.' . $type . $extension);
			$configs = array();
			foreach ($allowedConfigs as $config) {
				$configs[] = preg_replace('=.*/' . $folder . '([^.]*).' . $type . $extension . '=', '$1', $config);
			}

			if (in_array($name, $configs)) {
				$output = SQ_BASE_PATH . $folder . $name . '.' . $type . $extension;
			} else {
				if (file_exists(SQ_BASE_PATH . $folder . 'default.' . $type . $extension)) {
					$output = SQ_BASE_PATH . $folder . 'default.' . $type . $extension;
				} else {
					$output = 'Allowed ' . $name . ' and default stupidQuestion ' . $type . ' file "' . SQ_BASE_PATH . $folder . 'default.' . $type . $extension . '" not found. Did you upload all files?';
				}
			}
			return $output;
		}

		function prepareSettings($language, $answer = '') {
			$this->modx->getService('lexicon', 'modLexicon');
			$saveCultureKey = $this->modx->getOption('cultureKey');
			$this->modx->setOption('cultureKey', $language);
			$this->modx->lexicon->load('stupidquestion:default');
			$this->settings['questions_first'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.questions_first'));
			$this->settings['questions_second'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.questions_second'));
			$this->settings['questions'] = array_merge($this->settings['questions_first'], $this->settings['questions_second']);
			$this->settings['intro'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.intro'));
			if ($answer != '') {
				$this->settings['answer'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.answer'));
			} else {
				$this->settings['answer'] = $this->modx->fromJson($answer);
			}
			$this->settings['formFields'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.formFields'));
			$this->settings['required'] = $this->modx->lexicon('stupidquestion.required');
			$this->settings['requiredMessage'] = $this->modx->lexicon('stupidquestion.requiredMessage');
			$this->modx->setOption('cultureKey', $saveCultureKey);
			return;
		}

		function prepareTemplates($formcode) {
			if ($formcode == '') {
				$this->templates['formcode'] = '@FILE ' . $this->includeFile('formcode', 'template', '.html');
			} else {
				$this->templates['formcode'] = $formcode;
			}
			$this->templates['jscode'] = '@FILE ' . $this->includeFile('jscode', 'template', '.js');
			$this->templates['jswrapper'] = '@FILE ' . $this->includeFile('jswrapper', 'template', '.js');
			return;
		}

		function setQuestion() {
			// Random values
			$randQuestion = rand(0, count($this->settings['questions']) - 1);
			$randIntro = rand(0, count($this->settings['intro']) - 1);
			$randAnswer = rand(0, count($this->settings['answer']) - 1);
			$randFormField = rand(0, count($this->settings['formFields']) - 1);

			// reset session if $_POST is not filled
			if (!count($_POST)) {
				unset($_SESSION['StupidQuestion'], $_SESSION['StupidQuestionFormField'], $_SESSION['StupidQuestionAnswer']);
			}

			// get $_POST and replace values with session values
			if (isset($_SESSION['StupidQuestion'])) {
				foreach ($this->settings['formFields'] as $formKey => $formField) {
					if (in_array($formField, array_keys($_POST))) {
						$randQuestion = $_SESSION['StupidQuestion'];
						$randAnswer = $_SESSION['StupidQuestionAnswer'];
						$randFormField = $formKey;
					}
				}
			}
			$_SESSION['StupidQuestion'] = $randQuestion;
			$_SESSION['StupidQuestionFormField'] = $randFormField;
			$_SESSION['StupidQuestionAnswer'] = $randAnswer;

			// form fields
			$answer = explode(' ', $this->settings['answer'][$randAnswer]);
			$value = ($randQuestion < count($this->settings['questions_first'])) ? $answer[0] : $answer[1];
			$othervalue = ($randQuestion < count($this->settings['questions_first'])) ? $answer[1] : $answer[0];
			$frage = $this->settings['questions'][$randQuestion];
			$formField = $this->settings['formFields'][$randFormField];

			// parse stupid question template and javscript template
			$jsCode = parseTpl($this->templates['jscode'], array(
				'id' => $formField,
				'othervalue' => $othervalue,
				'value' => $value,
				'tplPath' => '')
			);
			$question = parseTpl('@INLINE ' . $this->settings['intro'][$randIntro], array(
				'question' => $frage . $this->settings['answer'][$randAnswer],
				'tplPath' => '')
			);
			$this->output['htmlCode'] = parseTpl($this->templates['formcode'], array(
				'id' => $formField,
				'value' => $value,
				'question' => $question,
				'required' => $this->settings['required'],
				'requiredMessage' => $this->settings['requiredMessage'],
				'tplPath' => '')
			);
			$this->answer['answer'] = $value;
			$this->answer['formfield'] = $formField;

			$packer = new JavaScriptPacker($jsCode, 'Normal', true, false);
			$this->output['jsCode'] = parseTpl($this->templates['jswrapper'], array(
				'packed' => trim($packer->pack()),
				'tplPath' => '')
			);
			return;
		}

		function checkAnswer() {
			if (count($_POST) > 0 && $_POST[$this->answer['formfield']] != $this->answer['answer']) {
				$this->output['errorMessage'] = $this->settings['requiredMessage'];
				return false;
			} else {
				$this->output['errorMessage'] = '';
				return true;
			}
		}

	}

}
?>
