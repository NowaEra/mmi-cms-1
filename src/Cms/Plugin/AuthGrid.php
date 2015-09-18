<?php

/**
 * Mmi Framework (https://bitbucket.org/mariuszmilejko/mmicms/)
 * 
 * @link       https://bitbucket.org/mariuszmilejko/mmicms/
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Cms\Plugin;

class AuthGrid extends \Mmi\Grid {

	public function init() {

		$this->setQuery(\Cms\Orm\Auth\Query::factory());

		$this->setOption('locked', true);

		$this->addColumn('text', 'username', [
			'label' => 'nazwa użytkownika'
		]);

		$this->addColumn('text', 'email', [
			'label' => 'e-mail'
		]);

		$this->addColumn('text', 'name', [
			'label' => 'pełna nazwa użytkownika'
		]);

		$this->addColumn('text', 'lastLog', [
			'label' => 'ostatnio zalogowany'
		]);

		$this->addColumn('text', 'lastIp', [
			'label' => 'ostatni IP'
		]);

		$this->addColumn('text', 'lastFailLog', [
			'label' => 'błędne logowanie'
		]);

		$this->addColumn('text', 'lastFailIp', [
			'label' => 'IP błędnego logowania'
		]);

		$this->addColumn('checkbox', 'active', [
			'label' => 'aktywny'
		]);

		$this->addColumn('buttons', 'buttons', [
			'label' => 'operacje'
		]);
	}

}
