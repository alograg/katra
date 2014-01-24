<?php
//namespace core\database\dal;
/**
 * Abstracción del acceso a base de datos.
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl.database
 * @since 2011-03-01
 * @subpackage dal
 * @version $Id$
 */

/**
 * Abstracciones de lógica de datos.
 *
 * Define funciones básicas y funciones que se deben de implementar una tabla.
 * @package Mekayotl.database
 * @subpackage dal
 * @abstract
 */
abstract class Mekayotl_database_dal_BusinessAbstract
{
    /**
     * Constructor del objeto.
     * @param Mekayotl_database_SQLAbstract $adapter
     */

    public function __construct(Mekayotl_database_SQLAbstract $adapter = NULL)
    {
        $this->setAdapter($adapter);
    }
    /**
     * Establece el adaptador a la base de datos.
     * @param Mekayotl_database_SQLAbstract $adapter
     */

    public function setAdapter(Mekayotl_database_SQLAbstract $adapter = NULL)
    {
        foreach ($this as $property => &$item) {
            $item->setAdapter($adapter);
        }
        return $this;
    }
    /**
     * Triggered when invoking inaccessible methods in a static context
     * @param string $method_name Nombre del método
     * @param array $arguments Argumentos pasados
     * @return variant El valor que regrese el método invocado
     * @throws Exception En caso de no existir el método
     */

    public static function __callStatic($methodName, $arguments)
    {
        $className = __CLASS__;
        $self = new $className();
        return call_user_func_array(array(&$self, $methodName), $arguments);
    }

    /**
     * Triggered when invoking inaccessible methods in an object context
     * @param string $method_name Nombre del método
     * @param array $arguments Argumentos pasados
     * @return variant El valor que regrese el método invocado
     */

    public function __call($methodName, array $arguments)
    {
        if (!method_exists($this, $methodName)) {
            return FALSE;
        }
        $request = Mekayotl_tools_Request::singleton();
        $permitExecution = FALSE;
        if ($request->request['token']) {
            $appClass = $GLOBALS['config']['class'];
            $permitExecution = call_user_func(
                    array(
                            $appClass,
                            'validToken'
                    ), $request->request['token']);
        }
        if (!$permitExecution) {
            return FALSE;
        }
        return call_user_func_array(array(&$this, $methodName), $arguments);
    }
}
