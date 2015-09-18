<?php

/**
 * Mmi Framework (https://bitbucket.org/mariuszmilejko/mmicms/)
 * 
 * @link       https://bitbucket.org/mariuszmilejko/mmicms/
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Cms\Model;
use \Cms\Orm;

class Text {

	/**
	 * Teksty w roznych jezykach
	 * @var array
	 */
	protected static $_texts = [];

	/**
	 * 
	 * @param string $key
	 * @param string $lang
	 * @return string|null
	 */
	public static function textByKeyLang($key, $lang) {
		if (empty(self::$_texts)) {
			self::_initDictionary();
		}
		if ($lang === null) {
			$lang = 'none';
		}
		if (isset(self::$_texts[$lang][$key])) {
			return self::$_texts[$lang][$key];
		}
		if (isset(self::$_texts['none'][$key])) {
			return self::$_texts['none'][$key];
		}
		return null;
	}

	/**
	 * Inicjalizacja slownika
	 */
	protected static function _initDictionary() {
		if (null === (self::$_texts = \App\Registry::$cache->load('\Cms\Text'))) {
			self::$_texts = [];
			foreach (Orm\Text\Query::factory()->find() as $text) {
				if ($text->lang === null) {
					self::$_texts['none'][$text->key] = $text->content;
					continue;
				}
				self::$_texts[$text->lang][$text->key] = $text->content;
			}
			\App\Registry::$cache->save(self::$_texts, '\Cms\Text', 0);
		}
	}

}
