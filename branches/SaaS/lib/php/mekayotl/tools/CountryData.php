<?php
//namespace core\tools;
/**
 * Herramienta para manejar información de cada país
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2011-03-01
 * @subpackage tools
 * @version $Id$
 */

/**
 * Contiene una serie de funciones para proporcionar información de países.
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_CountryData
{
    /**#@+
     * @access private
     */
    /**
     * Contiene el adaptador de acceso a la base de datos del pais.
     * @var Mekayotl_database_sqlite_Adapter
     * @see Mekayotl_database_sqlite_Adapter
     */

    private $_dbAdapterThisCountry;
    /**
     * Informa si se tiene acceso a información de códigos postales.
     */
    private $_hasZipCodes = FALSE;
    /**
     * Informa si se tiene acceso a información de estados.
     */
    private $_hasStates = FALSE;
    /**
     * Informa si se tiene acceso a información de ciudades.
     */
    private $_hasCities = FALSE;
    /**#@-*/
    /**#@+
     * @access public
     */
    /**
     * Contiene el adaptador para obtener información de países.
     * @static
     * @var Mekayotl_database_sqlite_Adapter
     * @see Mekayotl_database_sqlite_Adapter
     */
    public static $dbAdapterGeneral;
    /**
     * Falso si no se tiene información del país.
     * @var boolean
     */
    public $hasCountry = FALSE;
    /**
     * Contiene la información base del país
     * @var object
     */
    public $info;
    /**
     * Constructor del objeto del país
     * @param string $country Código de país según el [@link
     * http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 ISO 3166-1 alpha-2].
     * Default: mx
     * @param boolean $autoInstall Si la base de datos debe ser instalada si
     * no lo esta.
     * @TODO: Implementar el auto-instalador.
     */

    public function __construct($country = 'mx', $autoInstall = TRUE)
    {
        $country = strtolower($country);
        $this->changeCountry($country);
    }

    /**
     * Cambia el país del objeto.
     * @param string $country Código de país según el [@link
     * http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 ISO 3166-1 alpha-2].
     * Default: mx
     * @return This
     */

    public function changeCountry($country)
    {
        $this->hasCountry = self::hasCountry($country);
        if (!$this->hasCountry) {
            return $this;
        }
        $this->info = $this->hasCountry;
        $this->hasCountry = TRUE;
        $this->_dbAdapterThisCountry = new Mekayotl_database_sqlite_Adapter(
                array(
                        'dbname' => $country
                ));
        $this->_dbAdapterThisCountry
                ->setFetchMode(4);
        $rs = $this->_dbAdapterThisCountry
                ->select('sqlite_master');
        $tables = $this->_dbAdapterThisCountry
                ->fetchAll($rs);
        foreach ($tables as $table) {
            switch ($table->name) {
                case 'cities':
                    $this->_hasCities = TRUE;
                    break;
                case 'states':
                    $this->_hasStates = TRUE;
                    break;
                case 'zipcode':
                    $this->_hasZipCodes = TRUE;
                    break;
            }
        }
        return $this;
    }

    /**
     * Evalúa la existencia de la información del país.
     * @static
     * @param string $country Código de país según el [@link
     * http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 ISO 3166-1 alpha-2].
     * Default: mx
     * @return object Un objeto con la información del país
     */

    public static function hasCountry($country)
    {
        $generalAdapter = self::$dbAdapterGeneral;
        $filter = new Mekayotl_database_WhereCollection();
        $filter->code = strtoupper($country);
        $rs = $generalAdapter->setFetchMode(4)
                ->select('countries', '*', $filter);
        $countryInfo = $generalAdapter->fetch($rs);
        return $countryInfo;
    }

    /**
     * Obtiene la cuidad que contenga el código postal dado.
     * @param string|Mekayotl_database_WhereCollection $zipCode Código postal o
     * filtro de búsqueda
     * @return array Un arreglo con todas objetos de ciudad que sean abarcado
     * por el filtro.
     */

    public function getZipCodeCities($zipCode = 'All')
    {
        if (!$this->_hasZipCodes) {
            return FALSE;
        }
        $tmpAdapter = $this->_dbAdapterThisCountry;
        $zipCode = ((is_string($zipCode) && $zipCode == 'All')
                || $zipCode instanceof Mekayotl_database_WhereCollection) ? $zipCode
                : NULL;
        $citiesInfo = $tmpAdapter->fetchAll(
                        $tmpAdapter->select('zipcodes', 'DISTINCT city',
                                        $zipCode));
        return $citiesInfo;
    }

    /**
     * Obtiene los estado que contengan el código postal
     * @param string|Mekayotl_database_sqlite_WhereCollection $zipCode Código
     * postal o filtro de búsqueda
     * @return array Un arreglo con todos objetos de estado que sean abarcado
     * por el filtro.
     */

    public function getZipCodeStates($zipCode = 'All')
    {
        if (!$this->_hasZipCodes) {
            return FALSE;
        }
        $tmpAdapter = $this->_dbAdapterThisCountry;
        $zipCode = ((is_string($zipCode) && $zipCode == 'All')
                || $zipCode instanceof Mekayotl_database_WhereCollection) ? $zipCode
                : NULL;
        $statesInfo = $tmpAdapter->fetchAll(
                        $tmpAdapter->select('zipcodes', 'DISTINCT state',
                                        $zipCode));
        return $statesInfo;
    }

    /**
     * Obtiene las ciudades según código de estado.
     * @param string|Mekayotl_database_sqlite_WhereCollection $state Filtro de
     * búsqueda
     * @return array Un arreglo con todos objetos de ciudad que sean abarcado
     * por el filtro.
     */

    public function getCities($state = 'All')
    {
        if (!$this->_hasCities) {
            return FALSE;
        }
        $tmpAdapter = $this->_dbAdapterThisCountry;
        $zipCode = ((is_string($zipCode) && $zipCode == 'All')
                || $zipCode instanceof Mekayotl_database_WhereCollection) ? $zipCode
                : NULL;
        $countryInfo = $tmpAdapter->fetchAll(
                        $tmpAdapter->select('cities', 'DISTINCT city', $zipCode));
        return $countryInfo;
    }

    /**
     * Obtiene los estados.
     * @param string|Mekayotl_database_sqlite_WhereCollection $state Filtro de
     * búsqueda
     * @return array Un arreglo con todos objetos de estado que sean abarcado
     * por el filtro.
     */

    public function getStates($state = 'All')
    {
        if (!$this->_hasStates) {
            return FALSE;
        }
        $tmpAdapter = $this->_dbAdapterThisCountry;
        $zipCode = ((is_string($zipCode) && $zipCode == 'All')
                || $zipCode instanceof Mekayotl_database_WhereCollection) ? $zipCode
                : NULL;
        $countryInfo = $tmpAdapter->fetchAll(
                        $tmpAdapter->select('states', 'DISTINCT state',
                                        $zipCode));
        return $countryInfo;
    }

    /**
     * Obtiene los códigos postales según filtro.
     * @param string|Mekayotl_database_WhereCollection $text Filtro de búsqueda
     * @return array Un arreglo con todos objetos de código postal que sean
     * abarcado por el filtro.
     */

    public function matchZipCode($text)
    {
        if (!$this->_hasZipCodes) {
            return FALSE;
        }
        $tmpAdapter = $this->_dbAdapterThisCountry;
        $zipCode = ((is_string($zipCode) && $zipCode == 'All')
                || $zipCode instanceof Mekayotl_database_WhereCollection) ? $zipCode
                : NULL;
        $countryInfo = $tmpAdapter->fetchAll(
                        $tmpAdapter->select('zipcodes', 'DISTINCT zipcode',
                                        $zipCode));
        return $countryInfo;
    }

    /**#@-*/
}

Meakayotl_tools_CountryData::$dbAdapterGeneral = new Mekayotl_database_sqlite_Adapter(
        array(
                'dbname' => 'general'
        ));
