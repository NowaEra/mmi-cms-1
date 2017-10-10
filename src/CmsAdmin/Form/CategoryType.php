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
 * Formularz typu kategorii
 */
class CategoryType extends \Cms\Form\Form
{

    public function init()
    {

        //nazwa
        $this->addElement((new Element\Text('name'))
            ->setRequired()
            ->addFilter(new \Mmi\Filter\StringTrim([]))
            ->addValidator(new \Mmi\Validator\NotEmpty([]))
            ->addValidator(new \Mmi\Validator\RecordUnique([new \Cms\Orm\CmsCategoryTypeQuery, 'name', $this->getRecord()->id]))
            ->setLabel('nazwa'));

        //klasa modułu wyświetlania
        $this->addElement((new Element\Select('mvcParams'))
            ->setMultioptions([null => '---'] + \CmsAdmin\Model\Reflection::getOptionsWildcard(3))
            ->setRequired()
            ->addValidator(new \Mmi\Validator\NotEmpty([]))
            ->setLabel('moduł wyświetlania'));

        //ustawienie bufora
        $this->addElement((new Element\Select('cacheLifetime'))
            ->setLabel('odświeżanie')
            ->setMultioptions(\Cms\Orm\CmsCategoryRecord::CACHE_LIFETIMES)
            ->setValue(\Cms\Orm\CmsCategoryRecord::DEFAULT_CACHE_LIFETIME)
            ->addFilter(new \Mmi\Filter\EmptyToNull([])));

        //zapis
        $this->addElement((new Element\Submit('submit'))
            ->setLabel('zapisz szablon'));
    }

    /**
     * Przed zapisem
     * @return boolean
     */
    public function beforeSave()
    {
        //kalkulacja klucza
        $this->getRecord()->key = (new \Mmi\Filter\Url)->filter($this->getRecord()->name);
        return parent::beforeSave();
    }

}
