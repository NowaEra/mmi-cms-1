<?php

/**
 * Mmi Framework (https://bitbucket.org/mariuszmilejko/mmicms/)
 * 
 * @link       https://bitbucket.org/mariuszmilejko/mmicms/
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Cms\Plugin;

class MailServerGrid extends \Mmi\Grid {

	public function init() {

		$this->setQuery(\Cms\Orm\Mail\Server\Query::factory()
				->orderDescId());

		$this->addColumn('text', 'address', [
			'label' => 'Adres serwera',
		]);

		$this->addColumn('text', 'port', [
			'label' => 'Port',
		]);

		$this->addColumn('text', 'ssl', [
			'label' => 'Szyfrowanie',
		]);

		$this->addColumn('text', 'username', [
			'label' => 'Użytkownik',
		]);

		$this->addColumn('text', 'from', [
			'label' => 'Domyślny nadawca',
		]);

		$this->addColumn('text', 'dateAdd', [
			'label' => 'Data dodania',
		]);

		$this->addColumn('text', 'dateModify', [
			'label' => 'Data modyfikacji',
		]);

		$this->addColumn('buttons', 'buttons', [
			'label' => 'operacje',
		]);
	}

}
