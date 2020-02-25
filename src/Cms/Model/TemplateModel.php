<?php

namespace Cms\Model;

use Cms\App\CmsSkinsetConfig;
use Cms\Orm\CmsCategoryRecord;
use Cms\WidgetController;
use Mmi\App\KernelException;
use Mmi\Mvc\View;
use Cms\App\CmsTemplateConfig;
use Cms\TemplateController;

class TemplateModel
{
    /**
     * Rekord kategorii
     * @var CmsCategoryRecord
     */
    private $_categoryRecord;

    /**
     * Konfiguracja widgetas
     * @var CmsTemplateConfig
     */
    private $_templateConfig;

    /**
     * Konstruktor
     * @param CmsCategoryRecord $cmsCategoryRecord
     * @param CmsSkinsetConfig $skinsetConfig
     */
    public function __construct(CmsCategoryRecord $categoryRecord, CmsSkinsetConfig $skinsetConfig)
    {
        $this->_categoryRecord = $categoryRecord;
        //brak zdefiniowanego szablonu
        if (!$categoryRecord->template) {
            throw new KernelException('Category template not specified');
        }
        if (null === $this->_templateConfig = (new SkinsetModel($skinsetConfig))->getSkinModelByKey($categoryRecord->template)
            ->getTemplateConfigByKey($categoryRecord->template)) {
            throw new KernelException('Template not found');
        }
    }

    /**
     * Pobranie konfiguracji szablonu
     * @return CmsTemplateConfig
     */
    public function getTemplateConfg()
    {
        return $this->_templateConfig;
    }

    /**
     * Wywołanie akcji wyświetlenia
     * @param View $view
     * @return string
     */
    public function displayAction(View $view)
    {
        //wywołanie akcji wyświetlenia
        $controller = $this->_createController($view);
        $controller->displayAction();
        //render szablonu
        return $view->renderTemplate($this->_getTemplatePrefix() . '/display');
    }

    /**
     * Tworzy instancję kontrolera
     * @return WidgetController
     */
    private function _createController(View $view)
    {
        //odczytywanie nazwy kontrolera
        $controllerClass = $this->_templateConfig->getControllerClassName();
        //powołanie kontrolera z rekordem relacji
        $targetController = new $controllerClass($view->request, $view, $this->_categoryRecord);
        //kontroler nie jest poprawny
        if (!($targetController instanceof TemplateController)) {
            throw new KernelException('Not an instance of WidgetController');
        }
        //zwrot instancji kontrolera
        return $targetController;        
    }

    /**
     * Pobiera prefiks nazwy szablonu
     * @return string
     */
    private function _getTemplatePrefix()
    {
        $explodedControllerClass = explode('\\', $this->_templateConfig->getControllerClassName());
        return lcfirst($explodedControllerClass[0]) . '/' . lcfirst(substr($explodedControllerClass[1], 0, -10));
    }

}