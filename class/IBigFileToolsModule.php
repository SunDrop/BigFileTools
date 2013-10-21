<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Jan Kuchar
 */
interface IBigFileToolsModule {
	
	/**
	 * Setts math library
	 */
	function setMathLibrary(IMathLibrary $mathLibrary);
	
	/**
	 * Getts file size
	 * @return null = do not know | string|float = filesize
	 */
	function getFileSize($path);
	
	/**
	 * Is it file?
	 * @return null = do not know | false = not a file | true = is a file
	 */
	function isFile($path);
	
	/**
	 * Is file readable
	 * @return null = do not know | false = not a file | true = is a file
	 */
	function isReadable();
	
}
