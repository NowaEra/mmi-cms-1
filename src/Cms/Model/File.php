<?php

/**
 * Mmi Framework (https://bitbucket.org/mariuszmilejko/mmicms/)
 * 
 * @link       https://bitbucket.org/mariuszmilejko/mmicms/
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Cms\Model;
use \Cms\Orm;

class File {

	/**
	 * Dołącza pliki dla danego object i id
	 * @param string $object obiekt
	 * @param integer $id obiektu
	 * @param array $files tabela plików
	 * @param array $allowedTypes
	 * @return boolean
	 */
	public static function appendFiles($object, $id = null, array $files = [], array $allowedTypes = []) {
		//pola formularza
		foreach ($files as $fileSet) {
			//pliki w polu formularza
			foreach ($fileSet as $file) {
				/* @var $file \Mmi\Controller\Request\File */
				if (!self::appendFile($file, $object, $id, $allowedTypes)) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Dołącza pliki dla danego object i id bezpośrednio z serwera
	 * @param \Mmi\Controller\Request\File $file obiekt pliku
	 * @param string $object obiekt
	 * @param integer $id id obiektu
	 * @param array $allowedTypes dozwolone typy plików
	 * @return boolean
	 */
	public static function appendFile(\Mmi\Controller\Request\File $file, $object, $id = null, $allowedTypes = []) {
		//pomijanie plików typu bmp (bitmapy windows - nieobsługiwane w PHP)
		if ($file->type == 'image/x-ms-bmp') {
			return false;
		}
		//plik nie jest dozwolony
		if (!empty($allowedTypes) && !in_array($file->type, $allowedTypes)) {
			return false;
		}
		//kalkulacja nazwy systemowej
		$name = md5(microtime(true) . $file->tmpName) . substr($file->name, strrpos($file->name, '.'));
		//określanie ścieżki
		$dir = DATA_PATH . '/' . $name[0] . '/' . $name[1] . '/' . $name[2] . '/' . $name[3];
		//tworzenie ścieżki
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		//zmiana uprawnień
		chmod($file->tmpName, 0664);
		//kopiowanie pliku
		copy($file->tmpName, $dir . '/' . $name);
		//przypisywanie pól w rekordzie
		$record = self::_newRecordFromRequestFile($file);
		//zapis nazwy pliku
		$record->name = $name;
		//obiekt i id
		$record->object = $object;
		$record->objectId = $id;
		//zapis rekordu
		return $record->save();
	}

	/**
	 * Przenosi plik z jednego obiektu na inny
	 * @param string $srcObject obiekt źródłowy
	 * @param int $srcId id źródła
	 * @param string $destObject obiekt docelowy
	 * @param int $destId docelowy id
	 * @param int ilość przeniesionych
	 */
	public static function move($srcObject, $srcId, $destObject, $destId) {
		$i = 0;
		//przenoszenie plików
		foreach (Orm\File\Query::byObject($srcObject, $srcId)->find() as $file) {
			//nowy obiekt i id
			$file->object = $destObject;
			$file->objectId = $destId;
			//zapis
			$file->save();
			$i++;
		}
		return $i;
	}

	/**
	 * Sortuje po zserializowanej tabeli identyfikatorów
	 * @param array $serial tabela identyfikatorów
	 */
	public static function sortBySerial(array $serial = []) {
		foreach ($serial as $order => $id) {
			//brak rekordu o danym ID
			if (null === ($record = Orm\File\Query::factory()->findPk($id))) {
				continue;
			}
			//ustawianie kolejności i zapis
			$record->order = $order;
			$record->save();
		}
	}

	/**
	 * Tworzy nowy rekord na podstawie pliku z requestu
	 * @param \Mmi\Controller\Request\File $file plik z requesta
	 * @return \Cms\Orm\File\Record rekord pliku
	 */
	protected static function _newRecordFromRequestFile(\Mmi\Controller\Request\File $file) {
		//nowy rekord
		$record = new Orm\File\Record();
		//typ zasobu
		$record->mimeType = $file->type;
		//klasa zasobu
		$class = explode('/', $file->type);
		$record->class = $class[0];
		//oryginalna nazwa pliku
		$record->original = $file->name;
		//rozmiar pliku
		$record->size = $file->size;
		//daty dodania i modyfikacji
		$record->dateAdd = date('Y-m-d H:i:s');
		$record->dateModify = date('Y-m-d H:i:s');
		//właściciel pliku
		$record->cmsAuthId = \App\Registry::$auth ? \App\Registry::$auth->getId() : null;
		//domyślnie aktywny
		$record->active = 1;
		return $record;
	}

	/**
	 * Usuwa kolekcję rekordów po obiekcie i id
	 * @param string $object
	 * @param string $objectId
	 * @return integer ilość usuniętych obiektów
	 */
	public static function deleteByObject($object = null, $objectId = null) {
		//wybieramy kolekcję i usuwamy całą
		return Orm\File\Query::byObject($object, $objectId)
				->find()
				->delete();
	}

}
