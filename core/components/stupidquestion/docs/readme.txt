stupidQuestion
================================================================================

Userfriendly Captcha for MODX Revolution

Features:
--------------------------------------------------------------------------------
With this FormIt hook a stupid question captcha is inserted in the placeholder 
[[!+formit.stupidquestion_html]] in the form template. The form field is filled 
and hidden by a javascript that is packed by a javascript packer. The packer 
scrambles the code and because of the input name contains different counts of 
hyphens the right answer is not placed at the same position. The filling bots 
have to execute javascript - a lot don't do that.

Installation:
--------------------------------------------------------------------------------
MODX Package Management

Usage
--------------------------------------------------------------------------------

The snippet has to be used as FormIt preHook.

[[!FormIt? &preHooks=`StupidQuestion` ...

with the following properties

Property | Description | Default
---- | ----------- | -------
stupidQuestionAnswers | Answers for the stupid question (JSON encoded array of \'forename name\' combinations) | language dependent
stupidQuestionLanguage | Language of the stupid question | en
stupidQuestionFormcode | Template chunk for the stupid question html form field | content of the file `formcode.template.html` in folder `core/components/stupidquestion/templates`

If you want to change the html code for the stupid question form field, put this default code in a chunk and modify it:

<div>
	<label for="[[+id]]">[[+question]]</label>
	<input type="text" name="[[+id]]" id="[[+id]]" [[!+fi.error.[[+id]]:notempty=`class="error"`]]/><span class="small">([[+required]])</span>[[!+fi.error.[[+id]]]]<br />
</div>

Don't forget to place the [[!+formit.stupidquestion_html]] placeholder in the
form code.

Notes:
--------------------------------------------------------------------------------
1. Uses: PHP packer implementation on http://joliclic.free.fr/php/javascript-packer/en/
2. Bases on a captcha idea of Peter Kröner: http://www.peterkroener.de/dumme-frage-captchas-automatisch-ausfuellen/
