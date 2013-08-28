<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine caching library for modules or plugins.
 */

class File_cache {
	
	private $arrErrors;
	
	private $strCachePath;
	
	private $strCacheExpire;
	
	// ---------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct() {
		
		$this->EE =& get_instance();
		
		// Initialize Ivars
		$this->arrErrors = array();
		$this->strCachePath = sprintf('%s%s/', APPPATH, 'cache');
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Saves the provided items to the appropriate cache file. Supports object serialization.
	 *
	 * @access public
	 * @param string $strModuleShortName 
	 * @param string $strKey 
	 * @param mixed $mixedValue 
	 * @return mixed. If successful, returns the path to the cache file. If unsuccessful, returns FALSE.
	 */
	function saveCache($strModuleShortName, $strKey, $mixedValue) {
		
		if (empty($strModuleShortName) || empty($strKey) || empty($mixedValue) || !is_string($strKey)) {
			$this->arrErrors[] = 'Invalid arguments were passed to the function.';
			return FALSE;
		}
		$strCachePath = $this->_ParseCachePath($strModuleShortName, $strKey);		
		$arrayToCache = array(
			'time' 	=>	time(),
			$strKey	=>	$mixedValue,
		);
		
		if ($this->_WriteToFile($strCachePath, $arrayToCache)) {
			return $strCachePath;	
		} else {	
			$this->arrErrors[] = 'There was an error writing to the cache file.';
			return FALSE;		
		}
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Retrieves a previously stored items from the EE cache directory.
	 *
	 * @access public
	 * @param string $strModuleShortName 
	 * @param string $strKey 
	 * @return mixed Whatever object you stored or FALSE if the key doesn't exist
	 */
	function getCache($strModuleShortName, $strKey, $intTime) {
		
		if (empty($strModuleShortName) || empty($strKey) || empty($intTime) || !is_string($strModuleShortName) || !is_string($strKey) || !is_int($intTime)) {		
			$this->arrErrors[] = 'Invalid arguments were passed to the function.';
			return FALSE;			
		}		
		$strCachePath = $this->_ParseCachePath($strModuleShortName, $strKey);		
		return $this->_ReadFile($strCachePath, $intTime);				
	}	
	
	// ---------------------------------------------------------------------
	
	/**
	 * Retrieves an array containing errors that have occurred.
	 *
	 * @access public
	 * @return mixed Array if errors occurred, FALSE if no errors occurred
	 */
	function getErrors() {
		
		if (count($this->arrErrors) == 0) {		
			return FALSE;		
		} else {			
			return $this->arrErrors;			
		}		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Hashes the provided string
	 *
	 * @access private
	 * @param string $strToHash 
	 * @return string
	 */
	private function _HashString($strToHash) {		
		return MD5($strToHash);	
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Parses the cache path based on module short name and the key
	 * of the data being stored.
	 *
	 * @access private
	 * @param string $strModuleShortName 
	 * @param string $strKey 
	 * @return string
	 */
	private function _ParseCachePath($strModuleShortName, $strKey) {
		
		$strPath = sprintf('%s/%s/%s', $this->strCachePath, $strModuleShortName, $this->_HashString($strKey));	
		return $strPath;		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Writes the data to the specified file. Supports object serialization.
	 *
	 * @access private
	 * @param string $strPath 
	 * @param mixed $mixedContents 
	 * @return boolean Indicating success or failure
	 */
	private function _WriteToFile($strPath, $mixedContents) {
		
		$strDirPath = dirname($strPath);
		$strBasename = basename($strPath);
			
		// -------------------------------------
		//  Serialize contents
		// -------------------------------------
		
		$mixedContents = @serialize($mixedContents);
		
		// -------------------------------------
		//  Make sure all the directories leading up
		//  to the cache filename exist
		// -------------------------------------
		
		if (!@is_dir($strDirPath)) {
			
			if (!@mkdir($strDirPath, 0777, TRUE)) {				
				$this->arrErrors[] = 'Unable to create the directories in the cache path: ' . $strPath;
				return FALSE;				
			}			
			@chmod($strDirPath, 0777);			
		}
		
		// -------------------------------------
		//  Write to file, return result
		// -------------------------------------
		
		if (@file_put_contents($strPath, $mixedContents, LOCK_EX) === FALSE) {			
			$this->arrErrors[] = 'An error occurred while trying to write the cache file.';
			return FALSE;			
		} else {
			@chmod($strPath, 0777);
			return TRUE;			
		}		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Reads the provided file, unserializes any objects and returns the result.
	 *
	 * @access private
	 * @param string $strPath 
	 * @return mixed Whatever object was serialized to create the file
	 */
	private function _ReadFile($strPath, $intTime) {
		
		// -------------------------------------
		//  Validate
		// -------------------------------------
		
		if (empty($strPath)) {			
			$this->arrErrors[] = 'Invalid arguments were passed to the function.';
			return FALSE;						
		} elseif (!@file_exists($strPath) || is_dir($strPath)) {			
			$this->arrErrors[] = 'The cache path provided doesn\'t exist or is a directory.';
			return FALSE;			
		}
		
		// -------------------------------------
		//  Read file
		// -------------------------------------
		
		$arrayCache =  @file_get_contents($strPath);
				
		if ($arrayCache === FALSE) {		
			$this->arrErrors[] = 'An error occured trying to read the cache file.';
			return FALSE;			
		}
				
		// -------------------------------------
		//  Unserialize and return
		// -------------------------------------
				
		$array = @unserialize($arrayCache);

		// -------------------------------------
		//  Check for an expired cache
		// -------------------------------------							
		if ($array['time'] < (time()-$intTime)) {
			unlink($strPath);
			return FALSE;
		}
		
		$this->_UpdateCacheTime($strPath, $array);				
		return $array;		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Updates the time set in the cache.
	 *
	 * @access private
	 * @param string $strPath 
	 * @return mixed Writes to file
	 */
	private function _UpdateCacheTime($strPath, $array) {
		$array['time'] = time();
		$this->_WriteToFile($strPath, $array);
		return TRUE;
	}
	
}

/* End of file file_cache.php */
/* Location: /system/expressionengine/third_party/module_name/libraries/file_cache.php */