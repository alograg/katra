<?php

Mekayotl::callExternal("cloudfile/base");

class Mekayotl_tools_CloudFiles
{

    public function __construct()
    {
        
    }

    /**
     * Atentifica la cuenta de usuario
     * @param String $username
     * @param String $key
     * @return Boolean
     */
    
    public function authentication($username, $apikey)
    {
        $this->_authentication = new CF_Authentication($username, $apikey);
        return $this->_authentication->authenticate();
    }

    /**
     * Crea la coneccion al Container
     * @param String $container 'toth-lo'
     * @return Boolean
     */
    
    public function connection($container)
    {
        $this->_connection = new CF_Connection($this->_authentication);
        $this->_container = $this->_connection->get_container($container);
        return ($this->_container != NULL) ? true : false;
    }

    /**
     * Devuelve la lista de contenedores
     * @param Integer $limit
     * @return Array
     */
    
    public function getContainers($limit = 0, $marker = NULL)
    {
        $containers = $this->_connection->list_containers($limit, $marker);
        return $containers;
    }
    
    /**
     * Crea un contenedor
     * @param string $containerName
     * @return CF_Container
     */
    
    public function createContainer($containerName = NULL)
    {
        $container = $this->_connection->create_container($containerName);
        return $container;
    }
    
    /**
     * Elimina un contenedor
     * @param string $container
     */
    
    public function deleteContainer($container = NULL){
        $this->_connection->delete_container($container);
    }

    /**
     * Devuelve la lista de objetos de acuerdo a los parametros
     * @param int $limit
     * @param int $marker
     * @param string $prefix
     * @param string $path
     * @return array
     */
    
    public function listObjects($limit = 0, $marker = NULL, $prefix = NULL, 
            $path = NULL)
    {
        $listObjects = $this->_container->list_objects($limit, $marker, $prefix, 
                $path);
        return $listObjects;
    }

    /**
     * Devuelve los objetos segun los parametros
     * @param int $limit
     * @param int $marker
     * @param string $prefix
     * @param string $path
     * @param string $delimiter
     * @return Array
     */
    
    public function getObjects($limit = 0, $marker = NULL, $prefix = NULL, 
            $path = NULL, $delimiter = NULL)
    {
        $objects = $this->_container->get_objects($limit, $marker, $prefix, 
                $path, $delimiter);
        return $objects;
    }

    /**
     * Elimina un objeto del contenedor
     * @param object $obj
     * @param object $container
     */
    
    public function deleteObject($obj, $container = NULL)
    {
        $this->_container->delete_object($obj, $container);
    }

    /**
     * Sube el archivo
     * @param string $uploadDir 'upload/doc/2'
     * @param string $localfile $_FILES['upload']['tmp_name'];
     * @param string $filename $_FILES['upload']['name'];
     * @return Boolean
     */
    
    public function upload($localfile, $filename)
    {
        $this->_object = $this->_container->create_object($filename);
        $this->_object->load_from_filename($localfile);
    }

    /**
     * Sube un archivo a cloud files
     * @param string $path Ruta del archivo original
     * @param string $uploadname Nombre del objeto como sera guardado
     * @return boolean
     */
    
    public function save($path, $uploadname)
    {
        $this->_object = $this->_container->create_object($uploadname);
        return $this->_object->load_from_filename($path);
    }

    /**
     * Cierra la conexion
     */
    
    public function closeConnection()
    {
        $this->_connection->close();
    }

}