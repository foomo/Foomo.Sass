<?php

$bundle = Foomo\Sass\Bundle::create(
		'test',
		\Foomo\Sass\Module::getBaseDir('sass') . DIRECTORY_SEPARATOR . 'test.scss'
	)
	->debug(true)
;
$result = \Foomo\Bundle\Compiler::compile($bundle);

echo \Foomo\HTMLDocument::getInstance()
	->addStylesheets(array(
		$result->resources[0]->link
	))
	->addBody(<<<HTML
	<h1>Hello Sass!</h1>
HTML
	)
;
exit;
		\Foomo\Sass::create(\Foomo\Sass\Module::getBaseDir('sass') . DIRECTORY_SEPARATOR . 'test.sass')
			->compress(false)
			->watch(true)
			->compile()
			->getOutputPath();
