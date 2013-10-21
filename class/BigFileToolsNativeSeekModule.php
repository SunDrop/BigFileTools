<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__.'/BigFileToolsBaseModule.php';

/**
 * Description of BigFileToolsNativeSeek
 *
 * @author Jan Kuchar
 */
class BigFileToolsNativeSeekModule extends BigFileToolsBaseModule {
	
	/**
	 * Returns file size by using native fseek function
	 * @see http://www.php.net/manual/en/function.filesize.php#79023
	 * @see http://www.php.net/manual/en/function.filesize.php#102135
	 * @return string | bool (false when fail)
	 */
	public function getFileSize($path) {
		// This should work for large files on 64bit platforms and for small files every where
		$fp = fopen($path, "rb");
		if (!$fp) {
			return null;
		}
		flock($fp, LOCK_SH);
		$res = fseek($fp, 0, SEEK_END);
		if ($res === 0) {
			$pos = ftell($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
			// $pos will be positive int if file is <2GB
			// if is >2GB <4GB it will be negative number
			if($pos>=0) {
				return (string)$pos;
			} else {
				return sprintf("%u", $pos);
			}
		} else {
			flock($fp, LOCK_UN);
			fclose($fp);
			return null;
		}
	}

}
