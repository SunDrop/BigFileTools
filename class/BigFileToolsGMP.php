<?php

require_once __DIR__.'/IMathLibrary.php';

class BigFileToolsGMP implements IMathLibrary {
	
	function __construct() {
		if(!function_exists("gmp_add")) {
			throw new BigFileToolsException("BCMath not installed");
		}
	}

	public function add($a, $b) {
		$r = gmp_add($a, $b);
		return gmp_strval($r);
	}

	
}