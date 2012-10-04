<?php

namespace Phart;

use Phart\Util;

class Builder {

    private $util;

    private $phar_alias;
    private $phar_path;
    private $lib_paths;
    private $bin_path;

    function __construct(
    	Util $util
    ) {
    	$this->util = $util;
    }

    function alias($phar_alias) {
        $this->phar_alias = $phar_alias;
        return $this;
    }

    function path($phar_path) {
    	$this->phar_path = $phar_path;
    	return $this;
    }

    function lib(array $lib_paths) {
    	$this->lib_paths = $lib_paths;
    	return $this;
    }

    function bin($bin_path) {
    	$this->bin_path = $bin_path;
    	return $this;
    }

    function build() {
    	$cwd = getcwd().'/';
    	$alias = $this->phar_alias;
    	$path = $cwd.$this->phar_path;
    	if ($this->bin_path === null) {
    		die("phart currently only supports command-line executables");
    	}
    	$bin_stub = $this->util->generatorBinStub($cwd.$this->phar_path);

    	if (is_array($this->lib_paths)) {
    	    $lib_iterator = $this->util
    	        ->createDirectoryIterator($cwd, $this->lib_paths);
    	    $autoload_map = $this->util
    	        ->generateAutoloadMap($lib_iterator);
    	    $autoload_block = $this->util
    	        ->generateAutoloadStub($alias, $autoload_map);   
    	}

    	$stub = sprintf('<?php
Phar::mapPhar(\'%s\');
%s
%s
__HALT_COMPILER();?>',
            $alias,
            $bin_stub,
            $autoload_stub
        );

        if (!Phar::canWrite()) {
        	die("Phar is in read-only mode, try  'php -d phar.readonly=0'");
        }
        @unlink($phar_path);
        $phar = new Phar($path, 0, $alias);
        if (isset($lib_iterator)) {
        	$phar->buildFromIterator($iterator, $cwd);
        }
        $phar->setStub($stub);
    }
}