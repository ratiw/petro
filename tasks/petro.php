<?php

namespace Fuel\Tasks;

class Petro
{

	public static function run()
	{
		echo "Petro for FuelPHP".PHP_EOL;
		
		static::help();
		
	}
	
	public static function help()
	{
		echo "from help()";
	}
	
	protected static function check_prerequisites()
	{
		\Cli::write("\nChecking pre-requisite ...");
		\Cli::write("  - FuelPHP version: ".\Fuel::VERSION);
		\Cli::write("\nCurrent path:");
		\Cli::write("  Core Path    : ".COREPATH);
		\Cli::write("  App Path     : ".APPPATH);
		\Cli::write("  Package Path : ".PKGPATH);
		\Cli::write("  Public Path  : " .DOCROOT.'public\\');

		$public_path = DOCROOT.'public\\';
		\Cli::write("Test copying file:");
		try
		{
			\File::copy(PKGPATH.'petro\\tasks\\petro.php', $public_path.'assets\\css\\petro.tmp');
		}
		catch(\FileAccessException $e)
		{
			\Cli::write('  ** Cannot copy file');
		}
		
		\Cli::write("Test downloading zip from GitHub");
		$zip_url = 'https://github.com/twitter/bootstrap/zipball/v2.0.2';
		static::_download_package_zip($zip_url, 'bootstrap', '2.0.2');
	}
	
	public static function install()
	{
		\Cli::write("from install function");
		
		static::check_prerequisites();
	}
	
	public static function uninstall()
	{
		echo "from uninstall function";
	}

	protected static function _download_package_zip($zip_url, $package, $version)
	{
		// Make the folder so we can extract the ZIP to it
		mkdir($tmp_folder = APPPATH . 'tmp/' . $package . '-' . time());

		$zip_file = $tmp_folder . '.zip';
		@copy($zip_url, $zip_file);
/*
		$unzip = new \Unzip;
		$files = $unzip->extract($zip_file, $tmp_folder);

		// Grab the first folder out of it (we dont know what it's called)
		list($tmp_package_folder) = glob($tmp_folder.'/*', GLOB_ONLYDIR);

		$package_folder = PKGPATH . $package;

		// Move that folder into the packages folder
		rename($tmp_package_folder, $package_folder);

		unlink($zip_file);
		rmdir($tmp_folder);

		foreach ($files as $file)
		{
			$path = str_replace($tmp_package_folder, $package_folder, $file);
			chmod($path, octdec(755));
			\Cli::write("\t" . $path);
		}
*/		
	}
	
}