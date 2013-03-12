stupidQuestion
================================================================================

Userfriendly Captcha for MODX Revolution

Features:
--------------------------------------------------------------------------------
StupidQuestion is a simple but effective and userfriendly captcha solution for FormIt. A stupid question (i.e. 'What is the given name of Albert Einstein?') is inserted in the form template. The form field is filled and hidden by a javascript that is packed by a javascript packer. The packer scrambles the code and because of the input name contains different counts of hyphens the right answer is not placed at the same position. The filling bots have to execute javascript - a lot don't do that.

Installation:
--------------------------------------------------------------------------------
MODX Package Management

Usage
--------------------------------------------------------------------------------

The snippet has to be used as FormIt preHook and hook.

```
[[!FormIt? &preHooks=`StupidQuestion,...` &hooks=`StupidQuestion,...` ...
```

with the following properties

Property | Description | Default
---- | ----------- | -------
stupidQuestionAnswers | Answers for the stupid question (JSON encoded array of \'forename name\' combinations) | language dependent
stupidQuestionLanguage | Language of the stupid question | en
stupidQuestionFormcode | Template chunk for the stupid question html form field | the content of the file `formcode.template.html` in the folder `{core}/components/stupidquestion/templates`
stupidQuestionScriptcode | Template chunk for the filling javascript | the content of the file `jscode.template.js` the folder `{core}/components/stupidquestion/templates`
stupidQuestionRegister | Move the filling javascript to the end of the html body | false
stupidQuestionNoScript | Remove the filling javascript | false

If you want to change the html code for the stupid question form field, put this default code in a chunk and modify it:

```html
<div>
	<label for="[[+id]]">[[+question]]</label>
	<input type="text" name="[[+id]]" id="[[+id]]" [[!+fi.error.[[+id]]:notempty=`class="error"`]]/><span class="small">([[+required]])</span>[[!+fi.error.[[+id]]]]<br />
</div>
```

If you want to change the answers you could use this english lexicon setting as example:

```
["Charlie Chaplin", "Albert Einstein", "Mary Smith", "Whoopi Goldberg"]
```

All language specific strings could be changed by editing the lexicon entries in the stupidquestion namespace. Use the existing placeholders in these entries.

Don't forget to place the [[!+formit.stupidquestion_html]] placeholder in the form code.

Notes:
--------------------------------------------------------------------------------
1. Uses: PHP packer implementation on http://joliclic.free.fr/php/javascript-packer/en/
2. Bases on a captcha idea of Peter Kröner: http://www.peterkroener.de/dumme-frage-captchas-automatisch-ausfuellen/
