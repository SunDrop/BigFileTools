<?php


require_once __DIR__.'/BigFileToolsBaseModule.php';

class BigFileToolsComModule extends BigFileToolsBaseModule {
	
	/**
	 * Returns file size by using Windows COM interface
	 * @see http://stackoverflow.com/questions/5501451/php-x86-how-to-get-filesize-of-2gb-file-without-external-program/5502328#5502328
	 * @return string | null
	 */
	public function getFileSize($path) {
		if (class_exists("COM")) {
			// Use the Windows COM interface
			$fsobj = new COM('Scripting.FileSystemObject');
			if (dirname() == '.')
				$path = ((\substr(\getcwd(), -1) == \DIRECTORY_SEPARATOR) ? \getcwd() . \basename($path) : \getcwd($path) . \DIRECTORY_SEPARATOR . \basename($path));
			$f = $fsobj->GetFile($path);
			return (string) $f->Size;
		}
		return null;
	}
	
}