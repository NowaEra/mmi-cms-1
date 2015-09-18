<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Cms\Orm\Log;

class Record extends \Mmi\Orm\Record {

	public $id;
	public $url;
	public $ip;
	public $browser;
	public $operation;
	public $object;
	public $objectId;
	public $data;
	public $success;
	public $cmsAuthId;
	public $dateTime;

}
