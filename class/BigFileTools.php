<?php

// Hey! This code haven't been properly tested! Please do not use in production

require_once __DIR__.'/BigFileToolsBcMath.php';
require_once __DIR__.'/BigFileToolsGMP.php';

require_once __DIR__.'/BigFileToolsCurlModule.php';
require_once __DIR__.'/BigFileToolsComModule.php';
require_once __DIR__.'/BigFileToolsExecModule.php';
require_once __DIR__.'/BigFileToolsNativeReadModule.php';
require_once __DIR__.'/BigFileToolsNativeSeekModule.php';

/**
 * Class for manipulating files bigger than 2GB
 * (currently supports only getting filesize)
 *
 * @author Honza Kuchař
 * @license New BSD
 * @encoding UTF-8
 * @copyright Copyright (c) 2013, Jan Kuchař
 */
class BigFileTools {

	/**
	 * Absolute file path
	 * @var string
	 */
	protected $path;

	/**
	 * Which mathematical library use for mathematical operations
	 * @var IMathLibrary
	 */
	public static $mathLib;
	
	/**
	 * Was this class initialized?
	 * @var bool
	 */
	protected static $initialized = false;
	
	/**
	 * Array of modules used for getting size
	 * @var array of IBigFileToolsModule
	 */
	protected static $modules = array();

	/**
	 * If none of fast modes is available to compute filesize, BigFileTools uses to compute size very slow
	 * method - reading file from 0 byte to end. If you want to enable this behavior,
	 * switch fastMode to false (default is true)
	 * @var bool
	 */
	public static $fastMode = true;

	/**
	 * Initialization of class
	 * Do not call directly.
	 */
	static protected function init() {
		
		// TODO: make this using lazy loading, cause not all installation will need to use it
		if(!static::$mathLib) {
			try{
				static::$mathLib = new BigFileToolsBcMath();
			} catch (BigFileToolsException $ex) {}
		}
		
		if(!static::$mathLib) {
			try{
				static::$mathLib = new BigFileToolsGMP();
			} catch (BigFileToolsException $ex) {}
		}
		
		if(!static::$mathLib) {
			throw new BigFileToolsException("No usable math library.");
		}
		
		// TODO: Lazy initialization!
		if(!static::$modules) {
			// TODO: call setter and check class interface
			static::$modules = array(
			    new BigFileToolsCurlModule(),
			    new BigFileToolsNativeSeekModule(),
			    new BigFileToolsComModule(),
			    new BigFileToolsExecModule()
			);
			if (!static::$fastMode) {
				static::$modules[] = new BigFileToolsNativeReadModule();
			}
			foreach(static::$modules AS $module) {
				$module->setMathLibrary(static::$mathLib); // TODO: or give this insted?
			}
		}
		
	}

	/**
	 * Create BigFileTools from $path
	 * @param string $path
	 * @return BigFileTools
	 */
	static function fromPath($path) {
		return new self($path);
	}

	/**
	 * Gets basename of file (example: for file.txt will return "file")
	 * @return string
	 */
	public function getBaseName() {
		return pathinfo($this->path, PATHINFO_BASENAME);
	}

	/**
	 * Gets extension of file (example: for file.txt will return "txt")
	 * @return string
	 */
	public function getExtension() {
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}


	/**
	 * Gets extension of file (example: for file.txt will return "file.txt")
	 * @return string
	 */
	public function getFilename() {
		return pathinfo($this->path, PATHINFO_FILENAME);
	}

	/**
	 * Gets path to file of file (example: for file.txt will return path to file.txt, e.g. /home/test/)
	 * ! This will call absolute path!
	 * @return string
	 */
	public function getDirname() {
		return pathinfo($this->path, PATHINFO_DIRNAME);
	}

	/**
	 * Constructor - do not call directly
	 * @param string $path
	 */
	function __construct($path, $absolutizePath = true) {
		if(static::$initialized == false) {
			static::init();
			static::$initialized = true;
		}
		
		if (!static::isReadableFile($path)) {
			throw new BigFileToolsException("File not found at $path");
		}
		
		if($absolutizePath) {
			$this->setPath($path);
		}else{
			$this->setAbsolutePath($path);
		}
	}

	/**
	 * Tries to absolutize path and than updates instance state
	 * @param string $path
	 */
	function setPath($path) {
		$this->setAbsolutePath(static::absolutizePath($path));
	}
	
	/**
	 * Setts absolute path
	 * @param string $path
	 */
	function setAbsolutePath($path) {
		$this->path = $path;
	}
	
	/**
	 * Gets current filepath
	 * @return string
	 */
	function getPath() {
		return $this->path;
	}
	
	/**
	 * Converts relative path to absolute
	 */
	static function absolutizePath($path) {
		
		$path = realpath($path);
		if(!$path) {
			// TODO: use hack like http://stackoverflow.com/questions/4049856/replace-phps-realpath or http://www.php.net/manual/en/function.realpath.php#84012
			//       probaly as optinal feature that can be turned on when you know, what are you doing
			
			throw new BigFileToolsException("Not possible to resolve absolute path.");
		}
		return $path;
	}
	
	static function isReadableFile($file) {
		// Do not use is_file
		// @link https://bugs.php.net/bug.php?id=27792
		// $readable = is_readable($file); // does not always return correct value for directories
		
		$fp = @fopen($file, "r"); // must be file and must be readable
		if($fp) {
			fclose($fp);
			return true;
		}
		return false;
	}

	/**
	 * Moves file to new location / rename
	 * @param string $dest
	 */
	function move($dest) {
		if (move_uploaded_file($this->path, $dest)) {
			$this->setPath($dest);
			return TRUE;
		} else {
			@unlink($dest); // needed in PHP < 5.3 & Windows; intentionally @
			if (rename($this->path, $dest)) {
				$this->setPath($dest);
				return TRUE;
			} else {
				if (copy($this->path, $dest)) {
					unlink($this->path); // delete file
					$this->setPath($dest);
					return TRUE;
				}
				return FALSE;
			}
		}
	}


	/**
	 * Size of file
	 *
	 * Profiling results:
	 *  sizeCurl        0.00045299530029297
	 *  sizeNativeSeek  0.00052094459533691
	 *  sizeCom         0.0031449794769287
	 *  sizeExec        0.042937040328979
	 *  sizeNativeRead  2.7670161724091
	 *
	 * @return string | float
	 * @throws BigFileToolsException
	 */
	public function getSize($float = false) {
		if ($float == true) {
			return (float) $this->getSize(false);
		}
		
		foreach(self::$modules AS $module) {
			$r = $module->getFileSize($this->path);
			if($r != null) {
				return $r;
			}
		}

		throw new BigFileToolsException("Can not size of file $this->path!");
	}

}
class BigFileToolsException extends Exception{}