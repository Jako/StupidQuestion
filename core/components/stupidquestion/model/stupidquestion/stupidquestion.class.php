<?php
/**
 * StupidQuestion classfile
 *
 * @copyright Copyright 2010-2017, Thomas Jakobi <thomas.jakobi@partout.info>
 *
 * @package stupidquestion
 * @subpackage classfile
 */

/**
 * Class stupidQuestion
 */
class StupidQuestion
{
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * The namespace
     * @var string $namespace
     */
    public $namespace = 'stupidquestion';

    /**
     * The version
     * @var string $version
     */
    public $version = '0.8.0';

    /**
     * The class options
     * @var array $options
     */
    public $options = array();

    /**
     * Template cache
     * @var array $_tplCache
     */
    private $_tplCache;

    /**
     * Valid binding types
     * @var array $_validTypes
     */
    private $_validTypes = array(
        '@CHUNK',
        '@FILE',
        '@INLINE'
    );

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
     * The stupidQuestion templates
     *
     * @var array $templates
     * @access private
     */
    private $templates;

    /**
     * stupidQuestion constructor
     *
     * @param mixed $modx The MODX object
     * @param array $options The options for this stupidQuestion.
     */
    function __construct($modx, $options = array())
    {
        $this->modx = &$modx;

        $this->modx->lexicon->load('stupidquestion:default');

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path') . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path') . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url') . 'components/' . $this->namespace . '/');

        // Load some default paths for easier management
        $this->options = array_merge(array(
            'namespace' => $this->namespace,
            'version' => $this->version,
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'imagesUrl' => $assetsUrl . 'images/',
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'pagesPath' => $corePath . 'elements/pages/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ), $options);

        $language = $this->modx->getOption('language', $options, $this->modx->getOption('cultureKey'), true);
        $answer = $this->getOption('answer', $options, '');

        // Load parameters
        $this->options = array_merge($this->options, array(
            'questions_first' => json_decode($this->modx->lexicon('stupidquestion.questions_first', array(), $language), true),
            'questions_second' => json_decode($this->modx->lexicon('stupidquestion.questions_second', array(), $language), true),
            'intro' => json_decode($this->modx->lexicon('stupidquestion.intro', array(), $language), true),
            'answer' => ($answer == '') ? json_decode($this->modx->lexicon('stupidquestion.answer', array(), $language), true) : json_decode($answer, true),
            'formFields' => json_decode($this->modx->lexicon('stupidquestion.formFields', array(), $language), true),
            'required' => $this->modx->lexicon('stupidquestion.required'),
            'requiredMessage' => $this->modx->lexicon('stupidquestion.requiredMessage'),
            'packed' => true
        ));
        $this->options['questions'] = array_merge($this->options['questions_first'], $this->options['questions_second']);

        $this->output = array();
        $this->answer = '';
        $this->formfield = '';
        $this->templates = array(
            'formcode' => $this->modx->getOption('formcode', $options, '@FILE ' . $this->locateFile('formcode', 'chunk', '.html'), true),
            'jscode' => $this->modx->getOption('jscode', $options, '@FILE ' . $this->locateFile('jscode', 'chunk', '.js'), true),
            'jswrapper' => '@FILE ' . $this->locateFile('jswrapper', 'chunk', '.html')
        );

        $this->modx->loadClass('JavaScriptPacker', $this->getOption('modelPath') . 'packer/', true, true);

        $this->setQuestion();
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * Locate an existing file by name, filetype and extension. The file
     * is searched in $type based folder with the following file name
     * $name.$type.$extension
     *
     * @access private
     * @param string $name The main name of the file
     * @param string $type The type of the file
     * @param string $extension The extension of the file
     * @return string An existing file name otherwise an error message
     */
    private function locateFile($name, $type = 'config', $extension = '.inc.php')
    {
        $folder = (substr($type, -1) != 'y') ? $type . 's/' : substr($type, 0, -1) . 'ies/';
        $allowedFile = glob($this->getOption('corePath') . 'elements/' . $folder . '*.' . $type . $extension);
        $configs = array();
        foreach ($allowedFile as $config) {
            $configs[] = preg_replace('=.*/' . $folder . '([^.]*).' . $type . $extension . '=', '$1', $config);
        }
        if (in_array($name, $configs)) {
            $output = $this->getOption('corePath') . 'elements/' . $folder . $name . '.' . $type . $extension;
        } else {
            if (file_exists($this->getOption('corePath') . 'elements/' . $folder . 'default.' . $type . $extension)) {
                $output = $this->getOption('corePath') . 'elements/' . $folder . 'default.' . $type . $extension;
            } else {
                $output = 'Allowed ' . $name . ' and default stupidQuestion ' . $type . ' file "' . ($this->getOption('corePath') . 'elements/' . $folder . '*.' . $type . $extension) . '" not found. Did you upload all files?';
            }
        }
        return $output;
    }

    /**
     * Set the session and generate the output
     *
     * @access private
     */
    private function setQuestion()
    {
        // Random values
        $randQuestion = rand(0, count($this->options['questions']) - 1);
        $randIntro = rand(0, count($this->options['intro']) - 1);
        $randAnswer = rand(0, count($this->options['answer']) - 1);
        $randFormField = rand(0, count($this->options['formFields']) - 1);

        // reset session if $_POST is not filled
        if (!count($_POST)) {
            unset($_SESSION['StupidQuestion'], $_SESSION['StupidQuestionFormField'], $_SESSION['StupidQuestionAnswer']);
        }

        // get $_POST and replace values with session values
        if (isset($_SESSION['StupidQuestion'])) {
            foreach ($this->options['formFields'] as $formKey => $formField) {
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
        $answer = explode(' ', $this->options['answer'][$randAnswer]);
        $value = ($randQuestion < count($this->options['questions_first'])) ? $answer[0] : $answer[1];
        $othervalue = ($randQuestion < count($this->options['questions_first'])) ? $answer[1] : $answer[0];
        $frage = $this->options['questions'][$randQuestion];
        $formField = $this->options['formFields'][$randFormField];

        $jsCode = $this->getChunk($this->templates['jscode'], array(
            'id' => $formField,
            'othervalue' => $othervalue,
            'value' => $value,
            'tplPath' => '',
            'parse_fast' => true
        ));

        $question = $this->getChunk('@INLINE ' . $this->options['intro'][$randIntro], array(
            'question' => $frage . $this->options['answer'][$randAnswer],
            'tplPath' => ''
        ));

        $this->output['htmlCode'] = $this->getChunk($this->templates['formcode'], array(
            'id' => $formField,
            'value' => $value,
            'question' => $question,
            'required' => $this->options['required'],
            'requiredMessage' => $this->options['requiredMessage'],
            'tplPath' => '',
            'parse_fast' => true
        ));

        $this->answer = $value;
        $this->formfield = $formField;

        if ($this->getOption('packed')) {
            $packer = new JavaScriptPacker($jsCode, 'Normal', true, false);
            $this->output['jsCode'] = $this->getChunk($this->templates['jswrapper'], array(
                'packed' => trim($packer->pack()),
                'tplPath' => '',
                'parse_fast' => true
            ));
        } else {
            $this->output['jsCode'] = $this->getChunk($this->templates['jswrapper'], array(
                'packed' => trim($jsCode),
                'tplPath' => '',
                'parse_fast' => true
            ));
        }

        return;
    }

    /**
     * Check $_POST for the answer
     *
     * @access public
     * @return bool True if stupid question is answered right.
     * @internal param string $basepath The basepath @FILE is prefixed with.
     */
    public function checkAnswer()
    {
        if (count($_POST) > 0 && $_POST[$this->formfield] != $this->answer) {
            $this->output['errorMessage'] = $this->options['requiredMessage'];
            return false;
        } else {
            $this->output['errorMessage'] = '';
            return true;
        }
    }

    /**
     * Parse a chunk (with template bindings)
     * Modified parseTplElement method from getResources package (https://github.com/opengeek/getResources)
     *
     * @param $type
     * @param $source
     * @param null $properties
     * @param bool $fast
     * @return bool
     */
    private function parseChunk($type, $source, $properties = null, $fast = false)
    {
        $output = false;

        if (!is_string($type) || !in_array($type, $this->_validTypes)) {
            $type = $this->modx->getOption('tplType', $properties, '@CHUNK');
        }

        $content = false;
        switch ($type) {
            case '@FILE':
                $path = $this->modx->getOption('tplPath', $properties, $this->modx->getOption('assets_path', $properties, MODX_ASSETS_PATH) . 'elements/chunks/');
                $key = $path . $source;
                if (!isset($this->_tplCache['@FILE'])) {
                    $this->_tplCache['@FILE'] = array();
                }
                if (!array_key_exists($key, $this->_tplCache['@FILE'])) {
                    if (file_exists($key)) {
                        $content = file_get_contents($key);
                    }
                    $this->_tplCache['@FILE'][$key] = $content;
                } else {
                    $content = $this->_tplCache['@FILE'][$key];
                }
                if (!empty($content) && $content !== '0') {
                    if ($fast) {
                        $output = $this->_parseChunk($content, $properties);
                    } else {
                        $chunk = $this->modx->newObject('modChunk', array('name' => $key));
                        $chunk->setCacheable(false);
                        $output = $chunk->process($properties, $content);
                    }
                }
                break;
            case '@INLINE':
                if ($fast) {
                    $output = $this->_parseChunk($source, $properties);
                } else {
                    $uniqid = uniqid();
                    $chunk = $this->modx->newObject('modChunk', array('name' => "{$type}-{$uniqid}"));
                    $chunk->setCacheable(false);
                    $output = $chunk->process($properties, $source);
                }
                break;
            case '@CHUNK':
            default:
                $chunk = null;
                if (!isset($this->_tplCache['@CHUNK'])) {
                    $this->_tplCache['@CHUNK'] = array();
                }
                if (!array_key_exists($source, $this->_tplCache['@CHUNK'])) {
                    if ($chunk = $this->modx->getObject('modChunk', array('name' => $source))) {
                        $this->_tplCache['@CHUNK'][$source] = $chunk->toArray('', true);
                    } else {
                        $this->_tplCache['@CHUNK'][$source] = false;
                    }
                } elseif (is_array($this->_tplCache['@CHUNK'][$source])) {
                    $chunk = $this->modx->newObject('modChunk');
                    $chunk->fromArray($this->_tplCache['@CHUNK'][$source], '', true, true, true);
                }
                /** modChunk $chunk */
                if (is_object($chunk)) {
                    if ($fast) {
                        $output = $this->_parseChunk($chunk->get('snippet'), $properties);
                    } else {
                        $chunk->setCacheable(false);
                        $output = $chunk->process($properties);
                    }
                }
                break;
        }
        return $output;
    }

    /**
     * Parse a string using an associative array of replacement variables.
     *
     * @param string $string The string.
     * @param array $array An array of properties to replace in the string.
     * @param string $prefix The placeholder prefix, defaults to [[+.
     * @param string $suffix The placeholder suffix, defaults to ]].
     * @return string The processed chunk with the placeholders replaced.
     */
    private function _parseChunk($string, $array, $prefix = '[[+', $suffix = ']]')
    {
        if ((!empty($string) || $string === '0') && (is_array($array))) {
            foreach ($array as $key => $value) {
                $string = str_replace($prefix . $key . $suffix, $value, $string);
            }
        }
        return $string;
    }

    /**
     * Get and parse a chunk (with template bindings)
     * Modified parseTpl method from getResources package (https://github.com/opengeek/getResources)
     *
     * @param $tpl
     * @param null $properties
     * @return bool
     */
    public function getChunk($tpl, $properties = null)
    {
        $output = false;
        if (!empty($tpl)) {
            $bound = array(
                'type' => '@CHUNK',
                'value' => $tpl
            );
            if (strpos($tpl, '@') === 0) {
                $endPos = strpos($tpl, ' ');
                if ($endPos > 2 && $endPos < 10) {
                    $tt = substr($tpl, 0, $endPos);
                    if (in_array($tt, $this->_validTypes)) {
                        $bound['type'] = $tt;
                        $bound['value'] = substr($tpl, $endPos + 1);
                    }
                }
            }
            $fast = (isset($properties['parse_fast']) && $properties['parse_fast']) ? true : false;
            if (is_array($bound) && isset($bound['type']) && isset($bound['value'])) {
                $output = $this->parseChunk($bound['type'], $bound['value'], $properties, $fast);
            }
        }
        return $output;
    }
}
