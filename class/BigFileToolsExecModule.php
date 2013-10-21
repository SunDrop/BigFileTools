<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__.'/BigFileToolsBaseModule.php';

/**
 * Description of BigFileToolsExecModule
 *
 * @author Jan Kuchar
 */
class BigFileToolsExecModule extends BigFileToolsBaseModule {
	
	/**
	 * Returns file size by using external program (exec needed)
	 * @see http://stackoverflow.com/questions/5501451/php-x86-how-to-get-filesize-of-2gb-file-without-external-program/5502328#5502328
	 * @return string | null
	 */
	public function getFileSize($path) {
		if (function_exists("exec")) {
			$escapedPath = escapeshellarg($path);

			if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') { // Windows
				// Try using the NT substition modifier %~z
				$size = trim(exec("for %F in ($escapedPath) do @echo %~zF"));
			}else{ // other OS
				// If the platform is not Windows, use the stat command (should work for *nix and MacOS)
				$size = trim(exec("stat -Lc%s $escapedPath"));
			}

			// If the return is not blank, not zero, and is number
			if ($size AND ctype_digit($size)) {
				return (string) $size;
			}
			return null;
		}
		return null;
	}

}
