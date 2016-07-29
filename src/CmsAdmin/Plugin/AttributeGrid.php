<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2016 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\Plugin;

/**
 * Grid atrybutów
 */
class AttributeGrid extends \CmsAdmin\Grid\Grid {

	public function init() {

		//zapytanie
		$this->setQuery(new \Cms\Orm\CmsAttributeQuery);

		//nazwa taga
		$this->addColumnText('name')
			->setLabel('nazwa');
		
		//opis
		$this->addColumnText('description')
			->setLabel('opis');

		//klasa pola
		$this->addColumnText('fieldClass')
			->setLabel('klasa pola');

		//waga
		$this->addColumnText('indexWeight')
			->setLabel('waga w indeksie');

		//wymagany
		$this->addColumnCheckbox('required')
			->setLabel('wymagany');

		//unikalny
		$this->addColumnCheckbox('unique')
			->setLabel('unikalny');

		//zmaterializowany
		$this->addColumnCheckbox('materialized')
			->setLabel('zmaterializowany');

		//operacje
		$this->addColumnOperation();
	}

}
