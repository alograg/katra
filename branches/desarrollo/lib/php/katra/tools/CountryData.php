<?php
namespace core\tools;
/**
 * Herramienta para manejar informacion de cada pais
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	tools
 * @version	$Id$
 */

/**
 * Contiene una serie de funciones para proporcionar informacion de paices.
 *
 * @package	Katra
 * @subpackage	tools
 */
class CountryData {
	/**#@+
	 * @access	private
	 */
	/**
	 * Contiene el adaptador de acceso a la base de datos del pais.
	 * @var	\core\database\sqlite\Adapter
	 * @see	\core\database\sqlite\Adapter
	 */
	private $dbAdapterThisCountry;
	/**
	 * Informa si se tiene acceso a informacion de codigos postales.
	 */
	private $hasZipCodes = false;
	/**
	 * Informa si se tiene acceso a informacion de estados.
	 */
	private $hasStates = false;
	/**
	 * Informa si se tiene acceso a informacion de ciudades.
	 */
	private $hasCities = false;
	/**#@-*/
	/**#@+
	 * @access	public
	 */
	/**
	 * Contiene el adaptador para obtener informacion de paises.
	 * @static
	 * @var	\core\database\sqlite\Adapter
	 * @see	\core\database\sqlite\Adapter
	 */
	public static $dbAdapterGeneral;
	/**
	 * Falso si no se tiene informacion del pais.
	 * @var	boolean
	 */
	public $hasCountry = false;
	/**
	 * Contiene la informacion base dle pais
	 * @var	object
	 */
	public $info;
	/**
	 * Constructor del objeto del pais
	 * @param	string	$country	Codigo de pais segun el [@link http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 ISO 3166-1 alpha-2]. Default: mx
	 * @param	boolean	$autoInstall	Si la base de datos debe ser instalada si no lo esta.
	 * @TODO:	Implementar el autoinstalador.
	 */
	public function __construct($country = 'mx', $autoInstall = true){
		$country = strtolower($country);
		$this->changeCountry($country);
	}
	/**
	 * Cambia el pais del objeto.
	 * @param	string	$country	Codigo de pais segun el [@link http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 ISO 3166-1 alpha-2]. Default: mx
	 * @return	This
	 */
	public function changeCountry($country){
		$this->dbAdapterThisCountry = new \core\database\sqlite\Adapter(array('dbname' => $country));
		$this->dbAdapterThisCountry->setFetchMode(4);
		$this->hasCountry = self::hasCountry($country);
		if($this->hasCountries){
			$this->info = $this->hasCountries;
			$this->hasCountry = true;
		}
		return $this;
	}
	/**
	 * Evalua la existencia de la informacion del pais.
	 * @static
	 * @param	string	$country	Codigo de pais segun el [@link http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 ISO 3166-1 alpha-2]. Default: mx
	 * @return	object	Un objeto con la informacion del pais
	 */
	public static function hasCountry($country){
		$generalAdapter = self::$dbAdapterGeneral;
		$countryInfo = $generalAdapter->fetch(
							$generalAdapter
								->setFetchMode(4)
								->select('*', 'countries', null, "code = '" . strtoupper($country) . "'")
							);
		return $countryInfo;
	}
	/**
	 * Obten la cuidad que contenga el codigo postal dado.
	 * @param	string|\core\database\WhereCollection	$zipCode	Codigo postal o filtro de busqueda
	 * @return	array	Un array con todas objetos de ciudad que sean abarcado por el filtro.
	 */
	public function getZipCodeCities($zipCode = 'All'){
		if(!$this->hasZipCodes)
			return false;
		$tmpAdapter = $this->dbAdapterThisCountry;
		$zipCode = ((is_string($zipCode) && $zipCode == 'All')
					|| $zipCode instanceof \core\database\WhereCollection)
					? $zipCode
					: null;
		$citiesInfo = $tmpAdapter->fetchAll(
							$tmpAdapter
								->select('DISTINCT city', 'zipcodes', null, $zipCode)
							);
		return $citiesInfo;
	}
	/**
	 * Obtiene los estado que contengan el codigo postal
	 * @param	string|\core\database\WhereCollection	$zipCode	Codigo postal o filtro de busqueda
	 * @return	array	Un array con todos objetos de estado que sean abarcado por el filtro.
	 */
	public function getZipCodeStates($zipCode = 'All'){
		if(!$this->hasZipCodes)
			return false;
		$tmpAdapter = $this->dbAdapterThisCountry;
		$zipCode = ((is_string($zipCode) && $zipCode == 'All')
					|| $zipCode instanceof \core\database\WhereCollection)
					? $zipCode
					: null;
		$statesInfo = $tmpAdapter->fetchAll(
							$tmpAdapter
								->select('DISTINCT state', 'zipcodes', null, $zipCode)
							);
		return $statesInfo;
	}
	/**
	 * Obtiene las ciudades segun codigo de estado.
	 * @param	string|\core\database\WhereCollection	$state	Filtro de busqueda
	 * @return	array	Un array con todos objetos de ciudad que sean abarcado por el filtro.
	 */
	public function getCities($state = 'All'){
		if(!$this->hasCities)
			return false;
		$tmpAdapter = $this->dbAdapterThisCountry;
		$zipCode = ((is_string($zipCode) && $zipCode == 'All')
					|| $zipCode instanceof \core\database\WhereCollection)
					? $zipCode
					: null;
		$countryInfo = $tmpAdapter->fetchAll(
							$tmpAdapter
								->select('DISTINCT city', 'cities', null, $zipCode)
							);
		return $countryInfo;
	}
	/**
	 * Obtiene los estados.
	 * @param	string|\core\database\WhereCollection	$state	Filtro de busqueda
	 * @return	array	Un array con todos objetos de estado que sean abarcado por el filtro.
	 */
	public function getStates($state = 'All'){
		if(!$this->hasStates)
			return false;
		$tmpAdapter = $this->dbAdapterThisCountry;
		$zipCode = ((is_string($zipCode) && $zipCode == 'All')
					|| $zipCode instanceof \core\database\WhereCollection)
					? $zipCode
					: null;
		$countryInfo = $tmpAdapter->fetchAll(
							$tmpAdapter
								->select('DISTINCT state', 'states', null, $zipCode)
							);
		return $countryInfo;
	}
	/**
	 * Obtiene los codigos postales segun filtro.
	 * @param	string|\core\database\WhereCollection	$text	Filtro de busqueda
	 * @return	array	Un array con todos objetos de codigo postal que sean abarcado por el filtro.
	 */
	public function matchZipCode($text){
		if(!$this->hasZipCodes)
			return false;
		$tmpAdapter = $this->dbAdapterThisCountry;
		$zipCode = ((is_string($zipCode) && $zipCode == 'All')
					|| $zipCode instanceof \core\database\WhereCollection)
					? $zipCode
					: null;
		$countryInfo = $tmpAdapter->fetchAll(
							$tmpAdapter
								->select('DISTINCT zipcode', 'zipcodes', null, $zipCode)
							);
		return $countryInfo;
	}
	/**#@-*/
}
\core\tools\CountryData::$dbAdapterGeneral = new \core\database\sqlite\Adapter(array('dbname' => 'general'));