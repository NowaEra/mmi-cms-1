<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2016 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\Form;

class Article extends \Cms\Form\Form {

	public function init() {

		//tytuł
		$this->addElementText('title')
			->setRequired()
			->addValidatorNotEmpty()
			->setLabel('tytuł');

		//treść
		$this->addElementTinyMce('text')
			->setLabel('treść artykułu')
			->setModeAdvanced();

		//opcja noindex
		$this->addElementCheckbox('noindex')
			->setLabel('Bez indeksowania w google');

		//uploader - plupload
		$this->addElementPlupload('uploader')
			->setLabel('Załaduj pliki');

		$this->addElementSubmit('submit')
			->setLabel('zapisz stronę');
	}

}
