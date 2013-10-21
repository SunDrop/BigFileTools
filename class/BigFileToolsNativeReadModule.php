<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once __DIR__.'/BigFileToolsBaseModule.php';
/**
 * Description of BigFileToolsNativeModule
 *
 * @author Jan Kuchar
 */
class BigFileToolsNativeReadModule extends BigFileToolsBaseModule{
	
	/**
	 * Returns file size by using native fread function
	 * @see http://stackoverflow.com/questions/5501451/php-x86-how-to-get-filesize-of-2gb-file-without-external-program/5504829#5504829
	 * @return string | null
	 */
	public function getFileSize($path) {
		$fp = fopen($path, "rb");
		if (!$fp) {
			return false;
		}
		flock($fp, LOCK_SH);

		rewind($fp);
		$offset = PHP_INT_MAX - 1;

		$size = (string) $offset;
		if (fseek($fp, $offset) !== 0) {
			flock($fp, LOCK_UN);
			fclose($fp);
			return false;
		}
		$chunksize = 1024 * 1024;
		while (!feof($fp)) {
			$read = strlen(fread($fp, $chunksize));
			$size = static::$mathLib->add($size, $read);
		}
		flock($fp, LOCK_UN);
		fclose($fp);
		return $size;
	}

	
}
