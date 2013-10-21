<?php

require_once __DIR__.'/IMathLibrary.php';

class BigFileToolsBcMath implements IMathLibrary {
	
	function __construct() {
		if(!function_exists("bcadd")) {
			throw new BigFileToolsException("BCMath not installed");
		}
	}
	
	

	
	public function add($a, $b) {
		return bcadd($a, $b);
	}

	
}