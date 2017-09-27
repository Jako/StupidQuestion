# StupidQuestion

StupidQuestion is a simple but effective and userfriendly captcha solution for
FormIt. A stupid question (i.e. 'What is the given name of Albert Einstein?') is
inserted in the form template. The form field is filled and hidden by a
javascript that is packed by a javascript packer. The packer scrambles the code
and because of the input name contains different counts of hyphens the right
answer is not placed at the same position in the javascript. The filling bots
have to execute javascript - a lot don't do that.

### Requirements

* MODX Revolution 2.4+
* PHP v5.4+

### Features

* Userfriendly captcha solution.
* Hidden and filled with javascript.
