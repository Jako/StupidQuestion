<?php
/**
 * StupidQuestion - Userfriendly Captcha for MODX Revolution
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
 * @subpackage build
 * @author Thomas Jakobi <thomas.jakobi@partout.info>
 * @copyright Copyright 2010-2013, Thomas Jakobi
 *
 * snippets for StupidQuestion package
 */
$snippets = array();

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
	'id' => 1,
	'name' => 'StupidQuestion',
	'description' => 'Stupid question captcha hook for FormIt.',
	'snippet' => getSnippetContent($sources['snippets'] . 'snippet.stupidquestion.php'),
		), '', true, true);
$properties = include $sources['properties'] . 'properties.stupidquestion.php';
$snippets[1]->setProperties($properties);
unset($properties);

return $snippets;