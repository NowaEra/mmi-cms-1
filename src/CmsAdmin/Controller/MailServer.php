<?php

/**
 * Mmi Framework (https://bitbucket.org/mariuszmilejko/mmicms/)
 * 
 * @link       https://bitbucket.org/mariuszmilejko/mmicms/
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\Controller;

class MailServer extends Action {

	public function indexAction() {
		$grid = new \Cms\Plugin\MailServerGrid();
		$this->view->grid = $grid;
	}

	public function editAction() {
		$form = new \CmsAdmin\Form\Mail\Server(new \Cms\Orm\Mail\Server\Record($this->id));
		if ($form->isSaved()) {
			$this->getHelperMessenger()->addMessage('Zapisano ustawienia serwera', true);
			$this->getResponse()->redirect('cmsAdmin', 'mailServer');
		}
		$this->view->serverForm = $form;
	}

	public function deleteAction() {
		$server = \Cms\Orm\Mail\Server\Query::factory()->findPk($this->id);
		try {
			if ($server && $server->delete()) {
				$this->getHelperMessenger()->addMessage('Usunięto serwer');
			}
		} catch (\Mmi\Db\Exception $e) {
			$this->getHelperMessenger()->addMessage('Nie można usunąć serwera, istnieją powiązane szablony', false);
		}
		$this->getResponse()->redirect('cmsAdmin', 'mailServer');
	}

}
