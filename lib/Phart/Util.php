<?php

namespace Phart;

class Util {

	function generateBinStub($bin) {
    	if (!file_exists($bin)) {
    		die("bin does not exist: $bin");
    	}
    	$bin_stub = sprintf(
    		'require_once \'phar://%s/%s\';',
    		$alias,
    		$bin
    	);
	}
	
	function createDirectoryIterator($cwd, $library_paths) {
		$iterator = new AppendIterator();
        foreach ($library_paths as $path) {
        	$iterator->append(
        		new RecursiveIteratorIterator(
        		    new DecursiveDirectoryIterator(
	        			$cwd.'/'.$path
	        		)
	        	)
	        );
        }
        return $iterator;
	}

	function generateAutoloadMap($directory_iterator) {
		$classes = get_declared_classes();
		$autoload_map = array();
        foreach ($path_iterator as $path) {
        	$file_name = $path->getPathName();
        	include_once $file_name;

            $new_classes = get_declared_classes();
            $diff_classes = array_diff($new_Classes, $classes);
            foreach ($diff_Classes as $class) {
            	$autoload_map[strtolower($class)] =
            	    preg_replace(';'.__DIR__.';', '', $file_name);
            }
            $classes = $new_classes;
        }
        return $autoload_map;
	}

	function generateAutoloadStub($alias, $autoload_map) {
		$autoload_block = '
spl_autoload_register(
	function ($class) {
		static $classes = null;
		if ($classes === null) {
			$classes = '.var_export($autoload_map, true).';
		}
		$class_name = strtolower($class);
		if (isset($classes[$class_name])) {
			require \'phar://'.$alias.'\'.$classes[$class_name];
		}
	}
);
';
	}
}