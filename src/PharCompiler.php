<?php

namespace SevenPercent;

use FilesystemIterator;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PharCompiler {

	public function compile($executable, array $directories) {

		$basename = basename($executable);

		$phar = new Phar("$basename.phar", 0, "$basename.phar");
		$phar->setSignatureAlgorithm(Phar::SHA1);
		$phar->startBuffering();

		foreach ($directories as $directory) {
			$pathOffset = strlen(dirname(realpath($directory))) + 1;
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
				if ($file->getExtension() === 'php') {
					$phar->addFile($fileRealPath = $file->getRealPath(), substr($fileRealPath, $pathOffset));
				}
			}
		}
		$phar->addFromString("bin/$basename", preg_replace('{^#!/usr/bin/env php\s*}', '', file_get_contents($executable)));

		$phar->setStub("#!/usr/bin/env php\n<?php require'phar://$basename.phar/bin/$basename';__HALT_COMPILER();");

		$phar->stopBuffering();
	}
}
