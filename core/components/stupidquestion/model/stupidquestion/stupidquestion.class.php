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
 * @subpackage classfile
 * @author Thomas Jakobi <thomas.jakobi@partout.info>
 * @copyright Copyright 2010-2013, Thomas Jakobi
 *
 * StupidQuestion class.
 */
if (!class_exists('JavaScriptPacker')) {
	include $corePath . 'model/packer/class.JavaScriptPacker.php';
}
include $corePath . 'model/chunkie/chunkie.class.inc.php';

if (!class_exists('stupidQuestion')) {

	class stupidQuestion {

		/**
		 * The collection of output strings
		 *
		 * @var array $output
		 * @access public
		 */
		public $output;

		/**
		 * The answer of the stupid question
		 *
		 * @var string $answer
		 * @access public
		 */
		public $answer;

		/**
		 * The formfield that has to be posted containing $answer
		 *
		 * @var string $formfield
		 * @access public
		 */
		public $formfield;

		/**
		 * The MODX object
		 *
		 * @var mixed $modx
		 * @access private
		 */
		private $modx;

		/**
		 * The stupidQuestion settings
		 *
		 * @var array $settings
		 * @access private
		 */
		private $settings;

		/**
		 * The stupidQuestion templates
		 *
		 * @var array $templates
		 * @access private
		 */
		private $templates;

		/**
		 * The core path to the stupidQuestion installation
		 *
		 * @var string $template
		 * @access private
		 */
		private $corePath = '';

		/**
		 * stupidQuestion constructor
		 *
		 * @param mixed $modx The MODX object
		 * @param array $options The options for this stupidQuestion.
		 */
		function __construct($modx, $options = array()) {
			$this->output = array();
			$this->answer = '';
			$this->formfield = '';
			$this->modx = &$modx;
			$this->settings = array();
			$this->templates = array();
			$this->corePath = $this->modx->getOption('stupidquestion.core_path', null, MODX_CORE_PATH . 'components/stupidquestion/');

			$this->prepareSettings($options);
			$this->prepareTemplates($options);
			$this->setQuestion();
		}

		/**
		 * Research an existing file by name, filetype and extension. The file
		 * is searched in $type based folder with the following file name
		 * $name.$type.$extension
		 *
		 * @access private
		 * @param string $name The main name of the file
		 * @param string $type The type of the file
		 * @param string $extension The extension of the file
		 * @return string An existing file name otherwise an error message
		 */
		private function researchFile($name, $type = 'config', $extension = '.inc.php') {
			$folder = (substr($type, -1) != 'y') ? $type . 's/' : substr($folder, 0, -1) . 'ies/';
			$allowedFile = glob($this->corePath . $folder . '*.' . $type . $extension);
			$configs = array();
			foreach ($allowedFile as $config) {
				$configs[] = preg_replace('=.*/' . $folder . '([^.]*).' . $type . $extension . '=', '$1', $config);
			}
			if (in_array($name, $configs)) {
				$output = $this->corePath . $folder . $name . '.' . $type . $extension;
			} else {
				if (file_exists($this->corePath . $folder . 'default.' . $type . $extension)) {
					$output = $this->corePath . $folder . 'default.' . $type . $extension;
				} else {
					$output = 'Allowed ' . $name . ' and default stupidQuestion ' . $type . ' file "' . $this->corePath . $folder . 'default.' . $type . $extension . '" not found. Did you upload all files?';
				}
			}
			return $output;
		}

		/**
		 * Prepare the settings for stupidQuestion
		 *
		 * @access private
		 * @param array $options The options for this stupidQuestion.
		 */
		private function prepareSettings($options = array()) {
			$language = $this->modx->getOption('language', $options, 'en');
			$answer = $this->modx->getOption('answer', $options, '');

			$this->modx->getService('lexicon', 'modLexicon');
			$saveCultureKey = $this->modx->getOption('cultureKey');
			$this->modx->setOption('cultureKey', $language);
			$this->modx->lexicon->load('stupidquestion:default');
			$this->settings['questions_first'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.questions_first'));
			$this->settings['questions_second'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.questions_second'));
			$this->settings['questions'] = array_merge($this->settings['questions_first'], $this->settings['questions_second']);
			$this->settings['intro'] = $this->modx->fromJson($this->modx->lexicon('stupidquestion.intro'));
			if ($answer == '') {
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

		/**
		 * Prepare the templates for stupidQuestion
		 *
		 * @access private
		 * @param array $options The options for this stupidQuestion.
		 */
		private function prepareTemplates($options = array()) {
			$this->templates['formcode'] = $this->modx->getOption('formcode', $options, '@FILE ' . $this->researchFile('formcode', 'template', '.html'), true);
			$this->templates['jscode'] = $this->modx->getOption('jscode', $options, '@FILE ' . $this->researchFile('jscode', 'template', '.js'), true);
			$this->templates['jswrapper'] = '@FILE ' . $this->researchFile('jswrapper', 'template', '.js');
			return;
		}

		/**
		 * Set the session and generate the output
		 *
		 * @access private
		 */
		private function setQuestion() {
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

			// prepare form placeholder/script placeholder values
			$answer = explode(' ', $this->settings['answer'][$randAnswer]);
			$value = ($randQuestion < count($this->settings['questions_first'])) ? $answer[0] : $answer[1];
			$othervalue = ($randQuestion < count($this->settings['questions_first'])) ? $answer[1] : $answer[0];
			$frage = $this->settings['questions'][$randQuestion];
			$formField = $this->settings['formFields'][$randFormField];

			// parse stupid question template and javscript template
			$parser = new revoChunkie($this->templates['jscode']);
			$parser->createVars(array(
				'id' => $formField,
				'othervalue' => $othervalue,
				'value' => $value
			));
			$jsCode = $parser->render();

			$parser = new revoChunkie('@INLINE ' . $this->settings['intro'][$randIntro]);
			$parser->createVars(array(
				'question' => $frage . $this->settings['answer'][$randAnswer]
			));
			$question = $parser->render();

			$parser = new revoChunkie($this->templates['formcode']);
			$parser->createVars(array(
				'id' => $formField,
				'value' => $value,
				'question' => $question,
				'required' => $this->settings['required'],
				'requiredMessage' => $this->settings['requiredMessage']
			));
			$this->output['htmlCode'] = $parser->render();

			$this->answer = $value;
			$this->formfield = $formField;

			$packer = new JavaScriptPacker($jsCode, 'Normal', true, false);
			$parser = new revoChunkie($this->templates['jswrapper']);
			$parser->createVars(array(
				'packed' => trim($packer->pack())
			));
			$this->output['jsCode'] = $parser->render();

			return;
		}

		/**
		 * Check $_POST for the answer
		 *
		 * @access public
		 * @param string $basepath The basepath @FILE is prefixed with.
		 * @return boolean True if stupid question is answered right.
		 */
		public function checkAnswer() {
			if (count($_POST) > 0 && $_POST[$this->formfield] != $this->answer) {
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
