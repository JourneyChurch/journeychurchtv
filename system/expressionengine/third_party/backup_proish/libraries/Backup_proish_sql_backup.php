<?php

include_once('DB/class.factory.DB.php') ;
include_once 'pclzip.lib.php';

class Backup_proish_sql_backup
{
    /**
     * @var object for the type of database to be save or restored.
     * @access private
     */
    
    var $m_dbObject ;
    
    /**
     * @var resource the file pointer for the input/output file.
     * @access private
     */
    
    var $m_fptr;
    
    /**
     * @var string the name of the output file.
     * @access private
     */
    
    var $m_output;
    
    /**
     * @var boolean TRUE if only the structure of the database is to be saved.
     * @access private
     */
    
    var $m_structureOnly;
    
    /**
     * How many statements we want to compile into 1 INSERT command
     * @var unknown
     */
    var $m_group_by = '25';    
    
    function __construct()
    {
    	$this->EE =& get_instance();
        $this->m_structureOnly = FALSE;
        $this->settings = $this->EE->backup_proish_settings->get_settings();
    }
    
    public function set_settings($settings)
    {
    	$this->settings = $settings;
    }

    /**
     * @desc Restore a backup file.
     * @returns void
     * @access public
     */
    
    function restore($store_path, $db_info)
    {
    	//verify we're only restoring using the method the format supports!
    	$parts = explode($this->EE->backup_proish_lib->name_sep, $store_path);
    	if(!empty($parts['1']) && $parts['1'] == 'mysqldump')
    	{
    		$this->settings['db_restore_method'] = 'mysql';
    	}
    	
    	switch($this->settings['db_restore_method'])
    	{
    		case 'mysql':
    			$this->mysql_restore($store_path, $db_info);
    			break;
    	
    		case 'php':
    		default:
    			$this->php_restore($store_path, $db_info);
    			break;
    	}
    	    	
        return true;
    }
    
    public function mysql_restore($store_path, array $db_info)
    {
    	$command = "mysql -u ".$db_info['user']." -p".$db_info['pass']." ".$db_info['db_name']." < $store_path";
    	system($command);
    }
    
    public function php_restore($store_path, array $db_info)
    {
    	$this->m_dbObject =& FactoryDB::factory($db_info['user'], $db_info['pass'], $db_info['db_name'], $db_info['host'], dmDB_MySQL) ;
    	$this->m_output = $store_path;
    	$this->m_fptr = fopen($this->m_output, "r") ;
    	
    	if ($this->m_fptr === FALSE)
    	{
    		die(sprintf("Can't open %s", $this->m_output)) ;
    	}
    	
    	while (!feof($this->m_fptr))
    	{
    		$theQuery = fgets($this->m_fptr) ;
    		$theQuery = substr($theQuery, 0, strlen($theQuery) - 1) ;
    	
    		if ($theQuery != '')
    		{
    			$this->m_dbObject->query($theQuery) ;
    		}
    	}
    	
    	fclose($this->m_fptr);    	
    }   

    /**
     * @desc write an SQL statement to the backup file.
     * @param string The string to be written.
     * @access private
     */
    
    function _Out($s)
    {
    	if ($this->m_fptr === false)
    	{
    		echo("$s");
    	}
    	else
    	{
    		fputs($this->m_fptr, $s);
    	}
    } 

    /**
     * @desc public interface for backup.
     * @returns void
     * @access public
     */
    
    function backup($store_path, $db_info)
    {
        $this->m_dbObject =& FactoryDB::factory($db_info['user'], $db_info['pass'], $db_info['db_name'], $db_info['host'], dmDB_MySQL) ;
    	$this->m_output = $store_path;
    	$this->m_fptr=fopen($this->m_output,"w");
    	//enumerate tables 

        $this->m_dbObject->queryConstant('SHOW TABLES') ;
        
        while ($theTable =& $this->m_dbObject->fetchRow())
        {
        	$theTableName = $theTable[0];
        	
        	switch($this->settings['db_backup_method'])
        	{
        		case 'mysqldump':
        			$this->mysqldump_backup($theTableName, $db_info);
        			break;
        	
        		case 'php':
        		default:
        			$this->php_backup($theTableName);
        			break;
        	}   
        }  
        
        $this->m_dbObject->clear() ;
        
        if ($this->m_fptr!=false)
        {
            fclose($this->m_fptr);
        }
        
        $zip = new PclZip62($this->m_output.'.zip');
		if ($zip->create($this->m_output, PCLZIP_OPT_REMOVE_ALL_PATH) == 0) 
		{
			return FALSE;
		}      
		unlink($this->m_output);
		
        return $this->m_output.'.zip';
    }
    
    public function mysqldump_backup($theTableName, $db_info)
    {
    	$temp_store = $this->m_output.$theTableName;
    	$command = "mysqldump -u ".$db_info['user']." -p".$db_info['pass']." ".$db_info['db_name']." $theTableName > $temp_store";
    	system($command);
    	 
    	//now merge the table output with database output
    	$handle = fopen($temp_store,"r");
    	while (($buffer = fgets($handle)) !== false)
    	{
    		fputs($this->m_fptr, $buffer);
    	}
    
    	fclose($handle);
    	unlink($temp_store);
    }
    
    public function php_backup($theTableName)
    {
    	$theDB = clone($this->m_dbObject) ;
    	$theCreateTable = $theDB->showCreateTable($theTableName) ;
    	$theDB->clear() ;
    	
    	$theCreateTable = preg_replace('/\s*\n\s*/', ' ', $theCreateTable) ;
    	$theCreateTable = preg_replace('/\(\s*/', '(', $theCreateTable) ;
    	$theCreateTable = preg_replace('/\s*\)/', ')', $theCreateTable) ;
    	
    	$this->_Out(sprintf("DROP TABLE IF EXISTS `%s`; \n", $theTableName)) ;
    	//$this->_Out("/*!40101 SET @saved_cs_client = @@character_set_client */;\n");
    	//$this->_Out("/*!40101 SET character_set_client = utf8 */;\n");
    	
    	$this->_Out($theCreateTable . ";\n");
    	//$this->_Out("/*!40101 SET character_set_client = @saved_cs_client */;\n\n");
    	
    	if ($this->m_structureOnly != true)
    	{
    		$theDB->queryConstant(sprintf('SELECT * FROM %s', $theTableName)) ;
    	
    		$theFieldNames = '' ;
    		$count = 0; //we want to compile the SQL statements by groups of $m_group_by
    		$theData = array() ;
    		$totalRows = $theDB->resultCount();
    		$group_by = $this->m_group_by;
    		while ($theDataRow =& $theDB->fetchAssoc())
    		{
    			if ($theFieldNames == '')
    			{
    				$theFieldNames = '`' . implode('`, `', array_keys($theDataRow)) . '`' ;
    			}
    			
    			if($totalRows < $group_by)
    			{
    				$group_by = $totalRows;
    			}    			
    			
    			$theData = array() ;
    			foreach ($theDataRow as $theValue)
    			{
    				$data = '';
    				if(is_null($theValue))
    				{
    					$data = 'NULL';
    				}
    				elseif(is_numeric($theValue))
    				{
    					$data = $theValue;
    				}
    				else
    				{
    					$data = "'".$theDB->escape_string($theValue)."'";
    				}
    				
    				$theData[] = $data;
    			}
    	
    			$theRows[] = '('.implode(', ', $theData).')';
    			$count++;
    			if($count == $group_by || $totalRows == '1')
    			{
    				$line = implode(', ', $theRows);
	    			$theInsert = sprintf("INSERT INTO `%s` (%s) VALUES %s ;\n",
	    					$theTableName, $theFieldNames,
	    					$line);
	
	    			$this->_Out($theInsert);
	    			$theRows = array();
	    			$count = 0;
	    			$group_by = $this->m_group_by;
    			}
    			$totalRows--;
    			
    		}
    	
    		$this->_Out("\n");
    	}
    	
    	$theDB->clear() ;    	
    }    
}
?>