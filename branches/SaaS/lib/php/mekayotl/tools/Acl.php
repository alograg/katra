<?php
//namespace core\tools;
/**
 * Contiene la clase para manejo del ACL
 *
 * @author Erick Olvera <erick@aquainteractive.com.mx>
 * @copyright Copyright(c) 2011, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2011-03-01
 * @subpackage tools
 * @version $Id$
 */

/**
 * Clase para el manejo del ACL
 *
 * @package Mekayotl
 * @subpackage tools
 */

class Mekayotl_tools_Acl
{
    /**
     * Objeto del adaptador de la base de datos
     * @var object
     */

    protected $_adapter;
    /**
     * Hace la conexión a la Base de datos
     * @param object $oAdapter adaptador de la BD
     * @return object objeto de ACL
     * @uses
     */

    public function __construct($oAdapter = NULL)
    {
        $this->getAdapter($oAdapter);
    }

    /**
     * Agrega un nuevo rol
     * @param string $sName nombre del rol
     * @return
     * @uses
     */

    public function newRole($sName)
    {
        $res = $this->_adapter
                ->insert('roles',
                        array(
                                'rol_nombre' => $sName
                        ));
        if ($res !== TRUE) {
            return $res;
        }
        return TRUE;
    }

    /**
     * Borra un rol
     * @param string $sName nombre del rol
     * @return
     * @uses
     */

    public function deleteRole($sName)
    {
        $res = $this->_adapter
                ->delete('roles',
                        array(
                                'rol_nombre' => $sName
                        ));
        if ($res !== TRUE) {
            return $res;
        }
        return TRUE;
    }

    /**
     * Asigna un rol al usuario
     * @param array $data contiene el usuario y el rol/roles que se quieren
     * asignar
     * @return
     * @uses
     */

    public function assignRole($data)
    {
        if (is_array($data) && isset($data['rol_nombre'])
                && isset($data['user_id'])) {
            $rolRow = $this->_adapter
                    ->select('roles', '*',
                            array(
                                    'rol_nombre' => $data['rol_nombre']
                            ));
            $userRow = $this->_adapter
                    ->select('usuarios', '*',
                            array(
                                    'user_id' => $data['user_id']
                            ));
            if ($rolRow && $userRow) {
                return $this->_adapter
                        ->insert('relaciones',
                                array(
                                        'rel_id_com' => $data['user_id'],
                                        'rel_rol' => $data['rol_nombre'],
                                        'rel_tipo' => 'usu_rol'
                                ));
            }
        }
        return FALSE;
    }

    /**
     * Remueve un rol al usuario
     * @param array $data contiene el usuario y el rol/roles que se quieren
     * remover
     * @return
     * @uses
     */

    public function removeRole($data)
    {
        if (is_array($data) && isset($data['rol_nombre'])
                && isset($data['user_id'])) {
            $rolRow = $this->_adapter
                    ->select('roles', '*',
                            array(
                                    'rol_nombre' => $data['rol_nombre']
                            ));
            $userRow = $this->_adapter
                    ->select('usuarios', '*',
                            array(
                                    'user_id' => $data['user_id']
                            ));
            if ($rolRow && $userRow) {
                return $this->_adapter
                        ->delete('relaciones',
                                array(
                                        'rel_id_com' => $data['user_id'],
                                        'rel_rol' => $data['rol_nombre']
                                ));
            }
        }
        return FALSE;
    }

    /**
     * Agrega permisos a un rol
     * @param array contiene el rol y el/los permisos a agregar
     * @return
     * @uses
     */

    public function addPermissions($data)
    {
        if (is_array($data) && isset($data['rol_nombre'])
                && isset($data['met_name'])) {
            $rolRow = $this->_adapter
                    ->select('roles', '*',
                            array(
                                    'rol_nombre' => $data['rol_nombre']
                            ));
            $metRow = $this->_adapter
                    ->select('metodos', 'met_id',
                            array(
                                    'met_name' => $data['met_name']
                            ));
            if ($rolRow && $metRow) {
                return $this->_adapter
                        ->insert('relaciones',
                                array(
                                        'rel_id_com' => $met_id,
                                        'rel_rol' => $data['rol_nombre'],
                                        'rel_tipo' => 'met_rol'
                                ));
            }
        }
        return FALSE;
    }

    /**
     * Quita permisos a un rol
     * @param array contiene el rol y el/los permisos a quitar
     * @return
     * @uses
     */

    public function quitPermissions($data)
    {
        if (is_array($data) && isset($data['rol_nombre'])
                && isset($data['met_name'])) {
            $rolRow = $this->_adapter
                    ->select('roles', '*',
                            array(
                                    'rol_nombre' => $data['rol_nombre']
                            ));
            $met_id = $this->_adapter
                    ->select('metodos', 'met_id',
                            array(
                                    'met_name' => $data['met_name']
                            ));
            if ($rolRow && $metRow) {
                return $this->_adapter
                        ->delete('relaciones',
                                array(
                                        'rel_id_com' => $met_id,
                                        'rel_rol' => $data['rol_nombre']
                                ));
            }
        }
        return FALSE;
    }

    /**
     * Verifica si el usuario tiene permiso de acceder a un elemento
     * @param string $sUser nombre del usuario
     * @param string $sElement nombre del elemento
     * @return
     * @uses
     */

    public function isAlowed($sUser, $sElement)
    {
        if ($sUser != '' && $sElement != '') {
            return $this->_adapter
                    ->select('relaciones', '*',
                            array(
                                    'met_name' => $sElement,
                                    'rel_id_com' => $sUser
                            ));
        }
        return FALSE;
    }

    /**
     * Muestra los roles que tiene asignado un usuario
     * @param string $sUser contiene el nombre del usuario
     * @return
     * @uses
     */

    public function showRoles($sUser)
    {
        $usu_id = $this->_adapter
                ->select('usuarios', 'user_id',
                        array(
                                'user_name' => $sUser
                        ));
        return $rolesUsu = $this->_adapter
                ->select('relaciones', 'rel_rol',
                        array(
                                'rel_id_com' => $usu_id,
                                'rel_tipo' => 'usu_rol'
                        ));
    }

    /**
     * muestra los permisos de un rol, o los permisos que tiene asignado un
     * usuario
     * @param array $data contiene el nombre del rol o el nombre del usuario
     * @return
     * @uses
     */

    public function showPermissions($data)
    {
        //optimizar la consulta con join
        if (is_array($data)) {
            if (isset($data['rol_name'])) {
                return $this->_adapter
                        ->select('relaciones', 'rel_id_com',
                                array(
                                        'rel_rol' => $data['rol_name'],
                                        'rel_tipo' => 'met_rol'
                                ));
            } else if (isset($data['user_name'])) {
                $res = array();
                $usu_id = $this->_adapter
                        ->select('usuarios', 'user_id',
                                array(
                                        'user_name' => $data['username']
                                ));
                $rolesUsu = $this->_adapter
                        ->select('relaciones', 'rel_rol',
                                array(
                                        'rel_id_com' => $usu_id,
                                        'rel_tipo' => 'usu_rol'
                                ));
                foreach ($rolesUsu as $itm) {
                    $res[] = $this->_adapter
                            ->select('relaciones', 'rel_id_com',
                                    array(
                                            'rel_rol' => $itm['rel_rol'],
                                            'rel_tipo' => 'met_rol'
                                    ));
                }
                return $res;
            }
        }
        return FALSE;
    }

    /**
     * Cambia el adaptador de la BD
     * @param object Adaptador de la base de datos
     * @return this->_adapter
     * @uses
     */

    public function getAdapter($oAdapter = NULL)
    {
        if ($oAdapter instanceof Mekayotl_database_SQLAbstract) {
            $this->_adapter = $oAdapter;
        }
        return $this->_adapter;
    }

}
