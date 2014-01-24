<?php

//namespace Mekayotl_tools_utils;
/**
 * Utilitaria para AMF
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2012-10-29
 * @subpackage tools
 * @version $Id$
 */

/**
 * Utilitaria para AMF
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_utils_FileReference
{

    public $creationDate;
    public $creator;

    /**
     *
     * @var ByteArray
     */
    public $data;
    public $extension;
    public $modificationDate;
    public $name;
    public $size;
    public $type;
    public $savePath;
    public $username;
    public $apikay;
    public $container;
    public $cloudfiles;

    public function __construct()
    {
        $this->username = $GLOBALS['config']['cloudfiles']['username'];
        $this->apikey = $GLOBALS['config']['cloudfiles']['apikey'];
    }

    /**
     * Guarda el archivo pasado en la direccion indicada.
     * @param string $path
     * @param boolean $cloud
     * @return boolean|number
     */
    public function save($path = NULL,
            $cloud = NULL, $container = NULL)
    {
        $this->savePath = $path;
        $pathInfo = pathinfo($this->savePath);
        if ($pathInfo['extension'] == 'php') {
            $this->savePath = NULL;
            return 'Error: Extencion no permitida';
        }
        if (!strlen($this->data->data)) {
            $this->savePath = NULL;
            return 'Error: Archivo sin datos.';
        }
        $return = array();
        Mekayotl::generatePath($this->savePath,
                0775);
        $return['write'] = file_put_contents($this->savePath,
                $this->data->data);
        $return['creation'] = is_file($this->savePath);
        $return['permitions'] = chmod($this->savePath,
                0775);
        if (!$return['write']) {
            $this->savePath = NULL;
        }
        if ($cloud != NULL) {
            $pathInfo = pathinfo($path);
            $this->connectToCloudFiles($container);
            if ($pathInfo['extension'] == 'zip') {
                $fullpath = dirname($path) . '/';
                Mekayotl::generatePath($fullpath,
                        0775);
                $zip = new ZipArchive();
                $open = $zip->open($path);
                if ($open === true) {
                    $zip->extractTo($fullpath);
                    $zip->close();
                    unlink($path);
                }
                $files = $this->recursiveFiles($fullpath);
                $return['upload'] = true;
                foreach ($files as $row) {
                    if (!$this->saveToCloud($row, str_replace(PUBLIC_PATH . '/',
                                            '', $row)))
                        $return['upload'] = false;
                    unlink($row);
                }
            } else {
                $return['upload'] = $this->saveToCloud($path,
                        $cloud);
                unlink($path);
            }
            $this->closeCloudFilesConnection();
        }
        return $return;

    }

    /**
     * Devuelve la ubicacion de todos los archivos de una carpeta
     * @param string $path
     * @return array
     */
    function recursiveFiles($path)
    {
        $files = array();
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path),
                        RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (is_file($name))
                $files[] = $name;
        }
        return $files;

    }

    /**
     * Se conecta a Cloud Files
     */
    public function connectToCloudFiles($container)
    {
        $this->container = $container;
        $this->cloudfiles = new Mekayotl_tools_CloudFiles();
        $this->cloudfiles->authentication($this->username,
                $this->apikey);
        $this->cloudfiles->connection($this->container);

    }

    /**
     * Cierra la conexion con Cloud Files
     */
    public function closeCloudFilesConnection()
    {
        $this->cloudfiles->closeConnection();

    }

    /**
     * Guarda un archivo en Cloud Files
     * @param string $path
     * @param string $uploadname
     * @return boolean
     */
    public function saveToCloud($path,
            $uploadname)
    {
        return $this->cloudfiles->save($path,
                        $uploadname);

    }

    /**
     * Elimina los archivos parecidos al establecido como destino.
     * @param string $extention
     * @return boolean|string
     */
    public function removeSimilars($extention = '*.*')
    {
        $pathInfo = pathinfo($this->savePath);
        $wokPath = realpath($pathInfo['dirname']);
        if (!is_dir($wokPath) || is_null($this->savePath)) {
            return FALSE;
        }
        $pattern = $wokPath . '/' . $pathInfo['filename'] . $extention;
        $files = Mekayotl_tools_Utils::globRecursive($pattern);
        $id = array_search($this->savePath,
                $files);
        $files[$id] = NULL;
        $files = array_filter($files);
        $return = array();
        foreach ($files as $filename) {
            $remove = FALSE;
            if (is_file($filename)) {
                $remove = unlink($filename);
            } else if (is_dir($filename)) {
                $remove = rmdir($filename);
            }
            $return[] = (($remove) ? '[removed]' : '[omitted]') . $filename;
        }
        return $return;

    }

}
