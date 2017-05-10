<?php

/**
 * PluginFactory
 * instantiates plugins as singleton
 *
 */
final class PluginFactory {
	
	private static $contentPlugins = array();

	/**
	 * gets list with all installed content plugin names
	 * @return string[]
	 */
	public static function getContentPluginNames() {
		$names = array();
		$d = dir(__DIR__ . '/../../plugins/content/');
		while(false !== ($e = $d->read())) {
			if ($e != '.' && $e != '..') {
				$names[] = $e;
			}
		}
		return $names;
	}
	
	/**
	 * creates instance of content plugin
	 * @param string $pluginName
	 * @return iContentPlugin
	 */
	public static function createContentPlugin($pluginName) {
		if (self::$contentPlugins === null) {
			self::$contentPlugins = array();
		}
		$searchedValue = $pluginName;
		$neededObject = array_filter(
				self::$contentPlugins,
				function ($e) use (&$searchedValue) {
					return get_class($e) == $searchedValue.'Content';
				}
			);
		if (count($neededObject) == 1) {
			return $neededObject[0];
		}

		require __DIR__ . '/../../plugins/content/'.$pluginName.'/plugin.php';
		$className = $pluginName.'Content';
		$plugin = new $className();
		array_push(self::$contentPlugins, $plugin);
		
		return $plugin;
	}
	
	/**
	 * Private ctor so nobody else can instance it
	 *
	 */
	private function __construct()
	{
	
	}
}