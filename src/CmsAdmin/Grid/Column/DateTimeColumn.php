<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2016 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace CmsAdmin\Grid\Column;

use Mmi\App\FrontController;

/**
 * Klasa Columnu tekstowego
 *
 * @method self setName($name) ustawia nazwę pola
 * @method string getName() pobiera nazwę pola
 * @method self setLabel($label) ustawia labelkę
 * @method string getLabel() pobiera labelkę
 *
 * @method self setFilterMethodBetween() ustawia metodę filtracji na pomiędzy
 */
class DateTimeColumn extends TextColumn
{

    /**
     * Template filtra datetime
     */
    const TEMPLATE_FILTER = 'cmsAdmin/grid/filter/datetime';

    /**
     * Domyślne opcje dla checkboxa
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setMethod('between');
        parent::__construct($name);
    }

    /**
     * @return string
     */
    public function renderFilter()
    {
        FrontController::getInstance()->getView()->_column = $this;
        return FrontController::getInstance()->getView()->renderTemplate(self::TEMPLATE_FILTER);
    }

}