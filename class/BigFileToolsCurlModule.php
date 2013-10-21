<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__.'/BigFileToolsBaseModule.php';

/**
 * Description of BigFileToolsCurlModule
 *
 * @author Jan Kuchar
 */
class BigFileToolsCurlModule extends BigFileToolsBaseModule {
	
	/**
	 * Returns file size using curl module
	 * @see http://www.php.net/manual/en/function.filesize.php#100434
	 * @return string | null
	 */
	public function getFileSize($path) {
		// curl solution - cross platform and really cool :)
		if (!\function_exists("curl_init")) {return false;}
		$ch = curl_init("file://" . $path);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data !== false && preg_match('/Content-Length: (\d+)/', $data, $matches)) {
			return (string) $matches[1];
		}
		return false;
	}

}
