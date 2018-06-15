## Set the FormIt hooks

The snippet has to be used as FormIt preHook and hook.

```
[[!FormIt? 
&preHooks=`StupidQuestion,...` 
&hooks=`StupidQuestion,...` 
...
]]
```

with the following properties

Property | Description | Default
---- | ----------- | -------
stupidQuestionAnswers | Answers for the stupid question (JSON encoded array of 'forename name' combinations) | language dependent
stupidQuestionLanguage | Language of the stupid question | MODX cultureKey
stupidQuestionFormcode | Template chunk for the stupid question html form field | content of the file `formcode.template.html` in the folder `{core}/components/stupidquestion/templates`
stupidQuestionScriptcode | Template chunk for the filling javascript | content of the file `jscode.template.js` the folder `{core}/components/stupidquestion/templates`
stupidQuestionRegister | Move the filling javascript to the end of the html body | false
stupidQuestionNoScript | Remove the filling javascript | false

If you want to change the html code for the stupid question form field, put this default code in a chunk and modify it:

```html
<div class="form-group">
    <label for="[[+id]]">[[+question]]</label>
    <input type="text" name="[[+id]]" id="[[+id]]" class="form-control" [[!+fi.error.[[+id]]:notempty=`class="error"`]]>
    [[!+fi.error.[[+id]]:notempty=`<span class="help-block">[[!+fi.error.[[+id]]]]</span>`]]
</div>
```

If you want to change the answers you could use this english lexicon setting as example:

```
["Charlie Chaplin", "Albert Einstein", "Mary Smith", "Whoopi Goldberg"]
```

All language specific strings could be changed by editing the lexicon entries in the stupidquestion namespace. Use the existing placeholders in these entries.

Don't forget to place the `[[!+formit.stupidquestion_html]]` placeholder in the form code.
