<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\App;

/**
 * Konfiguracja nawigatora
 */
class CmsNavigationConfig extends \Mmi\Navigation\NavigationConfig {

	/**
	 * Pobiera menu
	 * @return \Mmi\Navigation\NavigationConfigElement
	 */
	public static function getMenu() {
		return self::newElement()
				->setLabel('Panel administracyjny')
				->setModule('cmsAdmin')
				->setController('index')
				->setVisible(true)
				->addChild(self::newElement()
					->setLabel('Zmiana hasła')
					->setModule('cmsAdmin')
					->setController('index')
					->setAction('password')
					->setVisible(false)
				)
				->addChild(self::_getContentPart());
	}

	/**
	 * Pobiera część kontentową
	 * @return \Mmi\Navigation\NavigationConfigElement
	 */
	protected static function _getContentPart() {
		return self::newElement()
				->setLabel('CMS')
				->setModule('cmsAdmin')
				->addChild(self::_getAdminPart())
				->addChild(NavPart\NavPartNews::getMenu())
				->addChild(NavPart\NavPartArticle::getMenu())
				->addChild(NavPart\NavPartComment::getMenu())
				->addChild(NavPart\NavPartContact::getMenu())
				->addChild(NavPart\NavPartStat::getMenu())
				->addChild(NavPart\NavPartText::getMenu());
	}

	/**
	 * Pobiera część administracyjną
	 * @return \Mmi\Navigation\NavigationConfigElement
	 */
	protected static function _getAdminPart() {
		return self::newElement()
				->setLabel('Administracja')
				->setModule('cmsAdmin')
				->addChild(NavPart\NavPartCron::getMenu())
				->addChild(NavPart\NavPartLog::getMenu())
				->addChild(NavPart\NavPartMail::getMenu())
				->addChild(NavPart\NavPartNavigation::getMenu())
				->addChild(NavPart\NavPartFile::getMenu())
				->addChild(NavPart\NavPartRoute::getMenu())
				->addChild(NavPart\NavPartAcl::getMenu())
				->addChild(NavPart\NavPartAuth::getMenu());
	}

}