<?php

/********************************************************************\
 * This program is free software; you can redistribute it and/or    *
 * modify it under the terms of the GNU General Public License as   *
 * published by the Free Software Foundation; either version 2 of   *
 * the License, or (at your option) any later version.              *
 *                                                                  *
 * This program is distributed in the hope that it will be useful,  *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of   *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    *
 * GNU General Public License for more details.                     *
 *                                                                  *
 * You should have received a copy of the GNU General Public License*
 * along with this program; if not, contact:                        *
 *                                                                  *
 * Free Software Foundation           Voice:  +1-617-542-5942       *
 * 59 Temple Place - Suite 330        Fax:    +1-617-542-2652       *
 * Boston, MA  02111-1307,  USA       gnu@gnu.org                   *
 *                                                                  *
\********************************************************************/
/**@file TrivialLangstring.php
 * @author Copyright (C) 2004-2005 Benoit Grégoire, Technologies Coeus inc.
*/

require_once BASEPATH.'classes/FormSelectGenerator.php';
require_once BASEPATH.'classes/Content.php';
require_once BASEPATH.'classes/LocaleList.php';
require_once BASEPATH.'classes/Locale.php';

error_reporting(E_ALL);

/** Represents a trivial Langstring (no title, description, etc.
 */
class TrivialLangstring extends Langstring
{
	/**Constructeur
	@param $content_id Content id
	*/
	function __construct($content_id)
	{
		parent :: __construct($content_id);
		$this->setIsTrivialContent(true);
		/* A TrivialLangstring is NEVER persistent */
		parent::setIsPersistent(false);
	}

	/** A short string representation of the content */
	public function __toString()
	{
		return $this->getString();
	}
	/** Reloads the object from the database.  Should normally be called after a set operation.
	 * This function is private because calling it from a subclass will call the
	 * constructor from the wrong scope */
	private function refresh()
	{
		$this->__construct($this->id);
	}
} /* end class */
?>