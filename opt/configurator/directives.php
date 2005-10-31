<?php
	define('OPT_VERSION', '1.0.0');
	$availableDirectives = array(
		'PLUGIN_AUTOLOAD' => array(
			'title' => 'Autoloading plugins support',
			'description' => 'Open Power Template may automatically load and install new functions, instructions and filters placed in the "/plugins" directory while running the engine. If you are not loading new features in this way, you may disable this option.'	
		),
		'CUSTOM_RESOURCES' => array(
			'title' => 'Custom resources',
			'description' => 'Resources allow you to load templates from different sources, such as databases. If you are using the default resource: "file" (templates in files), you may disable this option.'	
		),
		'DEBUG_CONSOLE' => array(
			'title' => 'Debug console',
			'description' => 'When in debug mode, the console shows much useful information about parsed templates, configuration etc. However, if you have already developed the script and you want to upload it into a webserver, you may remove the console from the code, because now it will be unnecessary.'	
		),
		'DISABLED_CC' => array(
			'title' => 'disableCompileCache',
			'description' => 'Removes the disableCompileCache directive from OPT and all the code supporting it.'	
		),
		'GZIP_SUPPORT' => array(
			'title' => 'GZip compression support',
			'description' => 'Removes GZip output compression support. Uncheck it, if your ISP does not provide ZLib extension.'
		),
		'OUTPUT_CACHING' => array(
			'title' => 'Output caching',
			'description' => 'Output caching is a big part of OPT source code. If you do not use this feature, uncheck it.'
		),
		'COMPONENTS' => array(
			'title' => 'Component support',
			'description' => 'Components allow you to build dynamic forms using OPT without touching IF\'s and other programming
				constructs. If you do not want to use them, uncheck this option.'
		),
		'PREDEFINED_COMPONENTS' => array(
			'title' => 'Predefined components',
			'description' => 'Remove predefined OPT components. Uncheck this option, if you want to use just your own ones.'
		),
		'REGISTER_FAMILY' => array(
			'title' => 'registerXXX() methods',
			'description' => 'Removes such methods, as registerInstruction() or registerComponent().'
		),
		'HTTP_HEADERS' => array(
			'title' => 'httpHeaders() methods',
			'description' => 'Removes httpHeaders() method.'
		),
	);
	
	$projectFiles = array(
		'opt.class.php', 'opt.compiler.php', 'opt.instructions.php', 'opt.functions.php', 'opt.error.php',
		'opt.filters.php', 'opt.api.php', 'opt.components.php'
	);
?>
