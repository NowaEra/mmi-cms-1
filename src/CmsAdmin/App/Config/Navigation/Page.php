<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\App\Config\Navigation;

class Page extends \Mmi\Navigation\Config {

	public static function getMenu() {
		return self::newElement()
				->setLabel('Strony CMS')
				->setModule('cmsAdmin')
				->setController('page')
				->addChild(self::newElement()
					->setLabel('Dodaj')
					->setModule('cmsAdmin')
					->setController('page')
					->setAction('edit'))
				->addChild(self::newElement()
					->setLabel('Widgety')
					->setModule('cmsAdmin')
					->setController('widget'))
				->addChild(self::newElement()
					->setLabel('Widgety - deklaracja')
					->setModule('cmsAdmin')
					->setController('pageWidget')
					->setAction('index')
					->addChild(self::newElement()
						->setLabel('Dodaj')
						->setModule('cmsAdmin')
						->setController('pageWidget')
						->setAction('edit')));
	}

}
