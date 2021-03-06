<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2016 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\Form;

use Cms\Form\Element;

/**
 * Formularz zmiany hasła w CMS
 * @method \Cms\Orm\CmsAuthRecord getRecord()
 */
class Password extends \Cms\Form\Form
{

    public function init()
    {

        $this->addElement((new Element\Password('password'))
            ->setLabel('form.index.password.current.label')
            ->setRequired()
            ->addValidator(new \Mmi\Validator\NotEmpty));

        $this->addElement((new Element\Password('changePassword'))
            ->setLabel('form.index.password.new.label')
            ->setDescription('form.index.password.new.description')
            ->setRequired()
            ->addValidator(new \Mmi\Validator\StringLength([4, 128])));

        $this->addElement((new Element\Password('confirmPassword'))
            ->setLabel('form.index.password.repeat.label')
            ->setRequired()
            ->addValidator(new \Mmi\Validator\StringLength([4, 128])));

        $this->addElement((new Element\Submit('change'))
            ->setLabel('form.index.password.submit'));
    }

    /**
     * Zmiana hasła
     * @return boolean
     */
    public function beforeSave()
    {
        $auth = new \Cms\Model\Auth;
        $record = $auth->authenticate(\App\Registry::$auth->getUsername(), $this->getElement('password')->getValue());
        //logowanie niepoprawne
        if (!$record) {
            $this->getElement('password')->addError('form.index.password.current.invalid');
            return false;
        }
        //hasła niezgodne
        if ($this->getElement('changePassword')->getValue() != $this->getElement('confirmPassword')->getValue()) {
            $this->getElement('confirmPassword')->addError('form.index.password.repeat.invalid');
            return false;
        }
        //znajdowanie rekordu użytkownika
        $authRecord = (new \Cms\Orm\CmsAuthQuery)->findPk(\App\Registry::$auth->getId());
        if (null === $authRecord) {
            return false;
        }
        $authRecord->password = \Cms\Model\Auth::getSaltedPasswordHash($this->getElement('changePassword')->getValue());
        return $authRecord->save();
    }

}
