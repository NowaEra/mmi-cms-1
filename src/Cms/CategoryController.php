<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2016 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Cms;

use App\Registry;
use Cms\Model\TemplateModel;
use Cms\Model\WidgetModel;
use Cms\Orm\CmsCategoryQuery;
use Cms\Orm\CmsCategoryRecord;

/**
 * Kontroler kategorii
 */
class CategoryController extends \Mmi\Mvc\Controller
{

    //akcja weryfikująca czy użytkownik jest redaktorem
    CONST REDACTOR_VERIFY_ACTION = 'cmsAdmin:category:index';

    /**
     * Akcja dispatchera kategorii
     */
    public function dispatchAction()
    {
        //pobranie kategorii
        $category = $this->_getPublishedCategoryByUri($this->uri);
        //klucz bufora
        $cacheKey = CmsCategoryRecord::HTML_CACHE_PREFIX . $category->id;
        //buforowanie dozwolone
        $bufferingAllowed = $this->_bufferingAllowed();
        //wczytanie zbuforowanej strony (dla niezalogowanych i z pustym requestem)
        if ($bufferingAllowed && (null !== $html = Registry::$cache->load($cacheKey))) {
            //wysyłanie nagłówka o buforowaniu strony
            $this->getResponse()->setHeader('X-Cache', 'HIT');
            //zwrot html
            return $this->_decorateHtmlWithEditButton($html, $category);
        }
        //wysyłanie nagłówka o braku buforowaniu strony
        $this->getResponse()->setHeader('X-Cache', 'MISS');
        //renderowanie docelowej akcji
        $html = $this->_renderHtml($category);
        //buforowanie niedozwolone
        if (!$bufferingAllowed || 0 == $cacheLifetime = $this->_getCategoryCacheLifetime($category)) {
            //zwrot html
            return $this->_decorateHtmlWithEditButton($html, $category);
        }
        //zapis html kategorii do cache
        Registry::$cache->save($html, $cacheKey, $cacheLifetime);
        //zwrot html
        return $this->_decorateHtmlWithEditButton($html, $category);
    }

    /**
     * Podgląd redaktora
     * @return string html
     * @throws \Mmi\Mvc\MvcForbiddenException
     * @throws \Mmi\Mvc\MvcNotFoundException
     */
    public function redactorPreviewAction()
    {
        //żądanie o wersję artykułu (rola redaktora)
        if (!$this->_hasRedactorRole()) {
            throw new \Mmi\Mvc\MvcForbiddenException('Preview not allowed');
        }
        //wyszukiwanie kategorii
        if (null === $category = (new Orm\CmsCategoryQuery)
            ->whereCmsCategoryOriginalId()->equals($this->originalId ? $this->originalId : null)
            ->findPk($this->versionId)) {
            //404
            throw new \Mmi\Mvc\MvcNotFoundException('Version not found');
        }
        //kategoria do widoku
        $this->view->category = $category;
        //nadpisanie tytułu i opisu (gdyż inaczej brany jest z kategorii oryginalnej)
        $this->view->navigation()->setTitle($category->title)
            ->setDescription($category->description);
        //renderowanie docelowej akcji
        return $this->_decorateHtmlWithEditButton($this->_renderHtml($category), $category);
    }

    /**
     * Akcja renderująca guzik edycji
     */
    public function editButtonAction()
    {
        $this->view->categoryId = $this->categoryId;
        $this->view->originalId = $this->originalId;
    }

    /**
     * Pobiera opublikowaną kategorię po uri
     * @param string $uri
     * @return \Cms\Orm\CmsCategoryRecord
     * @throws \Mmi\Mvc\MvcNotFoundException
     */
    protected function _getPublishedCategoryByUri($uri)
    {
        //inicjalizacja zmiennej
        $category = null;
        //próba mapowania uri na ID kategorii z cache
        if (null === $categoryId = Registry::$cache->load($cacheKey = CmsCategoryRecord::URI_ID_CACHE_PREFIX . md5($uri))) {
            //próba pobrania kategorii po URI
            if (null === $category = (new Orm\CmsCategoryQuery)->getCategoryByUri($uri)) {
                //zapis informacji o braku kategorii w cache
                Registry::$cache->save(false, $cacheKey, 0);
                //301 (o ile możliwe) lub 404
                $this->_redirectOrNotFound($uri);
            }
            //id kategorii
            $categoryId = $category->id;
            //zapis id kategorii i kategorii w cache 
            Registry::$cache->save($categoryId, $cacheKey, 0) && Registry::$cache->save($category, CmsCategoryRecord::CATEGORY_CACHE_PREFIX . $categoryId, 0);
        }
        //w buforze jest informacja o braku strony
        if (false === $categoryId) {
            //301 (o ile możliwe) lub 404
            $this->_redirectOrNotFound($uri);
        }
        //kategoria
        if ($category) {
            return $this->_checkCategory($category);
        }
        //pobranie kategorii z bufora
        if (null === $category = Registry::$cache->load($cacheKey = CmsCategoryRecord::CATEGORY_CACHE_PREFIX . $categoryId)) {
            //zapis pobranej kategorii w cache
            Registry::$cache->save($category = (new Orm\CmsCategoryQuery)->findPk($categoryId), $cacheKey, 0);
        }
        //sprawdzanie kategorii
        return $this->_checkCategory($category);
    }

    /**
     * Sprawdza aktywność kategorii do wyświetlenia
     * przekierowuje na 404 i na inne strony (zgodnie z redirectUri)
     * @param \Cms\Orm\CmsCategoryRecord $category
     * @return Orm\CmsCategoryRecord
     * @throws \Mmi\Mvc\MvcForbiddenException
     * @throws \Mmi\Mvc\MvcNotFoundException
     */
    protected function _checkCategory(\Cms\Orm\CmsCategoryRecord $category)
    {
        //kategoria to przekierowanie
        if ($category->redirectUri) {
            //przekierowanie na uri
            $this->getResponse()->redirectToUrl($category->redirectUri);
        }
        //sprawdzenie dostępu dla roli
        if (!(new Model\CategoryRole($category, \App\Registry::$auth->getRoles()))->isAllowed()) {
            //403
            throw new \Mmi\Mvc\MvcForbiddenException('Category: ' . $category->uri . ' forbidden for roles: ' . implode(', ', \App\Registry::$auth->getRoles()));
        }
        //kategoria posiada customUri, a wejście jest na natywny uri
        if ($category->customUri && $this->uri != $category->customUri && $this->uri == $category->uri) {
            //przekierowanie na customUri
            $this->getResponse()->redirect('cms', 'category', 'dispatch', ['uri' => $category->customUri]);
        }
        //opublikowana kategoria
        return $category;
    }

    /**
     * Pobiera request do przekierowania
     * @param \Cms\Orm\CmsCategoryRecord $category
     * @return string
     * @throws \Mmi\App\KernelException
     */
    protected function _renderHtml(\Cms\Orm\CmsCategoryRecord $category)
    {
        //tworzenie nowego requestu na podstawie obecnego
        $request = clone $this->getRequest();
        //przekierowanie MVC
        if ($category->mvcParams) {
            //tablica z tpl
            $mvcParams = [];
            //parsowanie parametrów mvc
            parse_str($category->mvcParams, $mvcParams);
            //zwrot html
            return \Mmi\Mvc\ActionHelper::getInstance()->forward($request->setParams($mvcParams));
        }
        //render szablonu
        return (new TemplateModel($category, Registry::$config->skinset))->renderDisplayAction($this->view);
    }

    /**
     * Pobiera request do renderowania akcji
     * @param \Cms\Orm\CmsCategoryRecord $category
     * @return string
     * @throws \Mmi\App\KernelException
     */
    protected function _decorateHtmlWithEditButton($html, \Cms\Orm\CmsCategoryRecord $category)
    {
        //brak roli redaktora
        if (!$this->_hasRedactorRole()) {
            return $html;
        }
        //zwraca wyrenderowany HTML
        return str_replace('</body>', \Mmi\Mvc\ActionHelper::getInstance()->action(new \Mmi\Http\Request(['module' => 'cms', 'controller' => 'category', 'action' => 'editButton', 'originalId' => $category->cmsCategoryOriginalId, 'categoryId' => $category->id])) . '</body>', $html);
    }

    /**
     * Zwraca czas buforowania kategorii
     * @return integer
     */
    protected function _getCategoryCacheLifetime(\Cms\Orm\CmsCategoryRecord $category)
    {
        //model szablonu
        $templateModel = new TemplateModel($category, Registry::$config->skinset);
        //czas buforowania (na podstawie typu kategorii i pojedynczej kategorii
        $cacheLifetime = (null !== $category->cacheLifetime) ? $category->cacheLifetime : $templateModel->getTemplateConfg()->getCacheLifeTime();
        //jeśli bufor wyłączony (na poziomie typu kategorii, lub pojedynczej kategorii)
        if (0 == $cacheLifetime) {
            //brak bufora
            return 0;
        }
        //iteracja po widgetach
        foreach ($category->getWidgetModel()->getWidgetRelations() as $widgetRelation) {
            //model widgeta
            $widgetModel = new WidgetModel($widgetRelation, Registry::$config->skinset);
            //bufor wyłączony przez widget
            if (0 == $widgetCacheLifetime = $widgetModel->getWidgetConfig()->getCacheLifeTime()) {
                //brak bufora
                return 0;
            }
            //wpływ widgeta na czas buforowania kategorii
            $cacheLifetime = ($cacheLifetime > $widgetCacheLifetime) ? $widgetCacheLifetime : $cacheLifetime;
        }
        //zwrot długości bufora
        return $cacheLifetime;
    }

    /**
     * Przekierowanie 301 (poszukiwanie w historii), lub 404
     * @param $uri
     * @throws \Exception
     * @throws \Mmi\Mvc\MvcNotFoundException
     */
    protected function _redirectOrNotFound($uri)
    {
        //klucz bufora
        $cacheKey = CmsCategoryRecord::REDIRECT_CACHE_PREFIX . md5($uri);
        //zbuforowany brak uri w historii
        if (false === ($redirectUri = \App\Registry::$cache->load($cacheKey))) {
            //404
            throw new \Mmi\Mvc\MvcNotFoundException('Category not found: ' . $uri);
        }
        //przekierowanie 301
        if (null !== $redirectUri) {
            return $this->getResponse()->setCode(301)->redirect('cms', 'category', 'dispatch', ['uri' => $redirectUri]);
        }
        //wyszukiwanie bieżącej kategorii (aktywnej)
        if (null === $category = (new CmsCategoryQuery)->byHistoryUri($uri)->findFirst()) {
            //brak kategorii w historii - buforowanie informacji
            \App\Registry::$cache->save(false, $cacheKey, 0);
            //404
            throw new \Mmi\Mvc\MvcNotFoundException('Category not found: ' . $uri);
        }
        //zapis uri przekierowania do bufora
        \App\Registry::$cache->save($category->uri, $cacheKey, 0);
        //przekierowanie 301
        return $this->getResponse()->setCode(301)->redirect('cms', 'category', 'dispatch', ['uri' => $category->uri]);
    }

    /**
     * Czy buforowanie dozwolone
     * @return boolean
     */
    protected function _bufferingAllowed()
    {
        return (new \Cms\Model\CategoryBuffering($this->_request))->isAllowed();
    }

    /**
     * Sprawdzenie czy jest redaktorem
     * @return boolean
     */
    protected function _hasRedactorRole()
    {
        return \App\Registry::$acl->isAllowed(\App\Registry::$auth->getRoles(), 'cmsAdmin:category:index');
    }

}
