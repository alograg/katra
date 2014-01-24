<?php
/**
 * The newest version of the MySQL adapter includes a hack to type number column
 * types as numbers, despite the fact that PHP does not offer this kind of info by default
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: mysqlAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class dalIteratorAdapter extends RecordSetAdapter
{

    /**
     * Constructor method for the adapter.  This constructor implements the setting of the
     * 3 required properties for the object.
     *
     * @param resource $d The datasource resource
     */

    function dalIteratorAdapter($d)
    {

        parent::RecordSetAdapter($d);
        $this->columns = $d->fields;
        foreach ($d as $row) {
            $this->rows[] = $row;
        }
    }

}
