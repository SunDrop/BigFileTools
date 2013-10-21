<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__.'/IBigFileToolsModule.php';

/**
 * Description of BigFileToolsBaseModule
 *
 * @author Jan Kuchar
 */
abstract class BigFileToolsBaseModule implements IBigFileToolsModule {
	protected $mathLibrary;
	
	public function setMathLibrary(\IMathLibrary $mathLibrary) {
		$this->mathLibrary = $mathLibrary;
	}
	
	
	// Most of modules are not able to determine if it is file
	
	public function isFile($path) {
		return null;
	}

	public function isReadable() {
		return null;
	}

}
