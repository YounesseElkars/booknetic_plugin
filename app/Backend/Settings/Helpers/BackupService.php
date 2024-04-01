<?php

namespace BookneticApp\Backend\Settings\Helpers;

use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;

class BackupService
{

	/**
	 * @var \ZipArchive
	 */
	private static $zip;

	private static $backupName = 'Happy-Data.Booknetic';

	public static function export()
	{
		self::$zip = new \ZipArchive();

		$zipFilePath = Helper::uploadedFile(self::$backupName, '');

		if ( self::$zip->open( $zipFilePath, \ZipArchive::CREATE) !== true )
		{
			throw new \Exception( bkntc__('Could not create a zip file!') );
		}

        DB::DB()->query("set autocommit = 0");
        DB::DB()->query("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
        DB::DB()->query("START TRANSACTION");

		$options = DB::DB()->get_results('SELECT `option_name`, `option_value`, `autoload` FROM `'.DB::DB()->base_prefix.'options` WHERE `option_name` LIKE \'bkntc_%\' AND `option_name` NOT IN (\'bkntc_access_token\',\'bkntc_purchase_code\', \'bkntc_plugin_version\')', ARRAY_A);

        $tableNames = apply_filters('bkntc_add_tables_for_export', Helper::pluginTables());

		foreach ( $tableNames AS $tableName )
		{
			$tableData[ $tableName ] = DB::fetchAll( $tableName );
		}

        DB::DB()->query("ROLLBACK");
        DB::DB()->query("set autocommit = 1");

        self::$zip->addFromString('sql/options.json', json_encode( $options ));
		self::$zip->addFromString('sql/tables.json', json_encode( $tableData ?? [] ));

        $metadata = [
            'version' => '3.1.2'
        ];

        self::$zip->addFromString( 'metadata.json', json_encode( $metadata ) );

		$upload_path = Helper::uploadFolder('');
		self::addDir( $upload_path );

		self::$zip->close();
	}

	public static function download()
	{
		$file = Helper::uploadedFile( self::$backupName, '' );

		if( !file_exists( $file ) )
		{
			return;
		}

        if ( ob_get_length() > 0 )
        {
		    ob_end_clean();
        }

		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"Happy-Data.Booknetic\"");
		header("Content-length: " . filesize($file));
		header("Pragma: no-cache");
		header("Expires: 0");

		readfile( $file );

		unlink( $file );
		exit();
	}

	private static function addDir( $real_path, $local_path = '' )
	{
		$dir = opendir( $real_path );

		while ( $filename = readdir( $dir ) )
		{
			if ( $filename == '.' || $filename == '..' || $filename == self::$backupName )
				continue;

			$path		= $real_path . '/' . $filename;
			$localpath	= $local_path ? ($local_path . '/' . $filename) : $filename;

			if (is_dir($path))
			{
				self::$zip->addEmptyDir( $localpath );
				self::addDir( $path, $localpath);
			}
			else if (is_file($path))
			{
				self::$zip->addFile( $path, $localpath );
			}
		}

		closedir($dir);
	}

	public static function restore( $file_path )
	{
		$zip = new \ZipArchive();

		if ( $zip->open( $file_path ) !== true )
		{
			throw new \Exception( bkntc__('Unable to read the backup file!') );
		}

        // if imported file is exported from 3.1.2 and below, don't allow
        if ( $zip->locateName('metadata.json') === false )
        {
            throw new \Exception( bkntc__('Exported data from older versions are incompatible with this version!') );
        }

        // doit ehtiyac olduqda json icindeki versiya ile yoxlama aparariq

		if( $zip->locateName('sql/options.json') === false || $zip->locateName('sql/tables.json') === false )
		{
			throw new \Exception( bkntc__('Unable to read the backup file!') );
		}

		set_time_limit( 0 );

		// Empty the booknetic folder
		self::rmDir( Helper::uploadFolder('') );

		$newBookneticDir = Helper::uploadFolder('');

		$filesToExtract = [ 'sql/options.json', 'sql/tables.json' ];

		$allowedFileExtensions = self::allowedFileTypes();
		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$filename	= $zip->getNameIndex( $i );
			$extension	= strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

			if( in_array( $extension, $allowedFileExtensions ) )
			{
				$filesToExtract[] = $filename;
			}
		}

		$zip->extractTo( $newBookneticDir, $filesToExtract );
		$zip->close();

		$options = file_get_contents( Helper::uploadedFile('options.json', 'sql') );
		$options = json_decode( $options, true );
		unlink( Helper::uploadedFile('options.json', 'sql') );

		$tables = file_get_contents( Helper::uploadedFile('tables.json', 'sql') );
		$tables = json_decode( $tables, true );
		unlink( Helper::uploadedFile('tables.json', 'sql') );

		rmdir( Helper::uploadFolder('sql') );

		// Truncate tables...
		$tablesToDelete = Helper::pluginTables();

        DB::DB()->query("set autocommit = 0");
        DB::DB()->query("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
        DB::DB()->query("START TRANSACTION");
		DB::DB()->query("SET FOREIGN_KEY_CHECKS = 0;");
		foreach( $tablesToDelete AS $tableName )
		{
			DB::DB()->query("DELETE FROM `" . DB::table( $tableName ) . "`");
		}

		// Delete current options...
		DB::DB()->query('DELETE FROM `'.DB::DB()->base_prefix.'options` WHERE `option_name` LIKE \'bkntc_%\' AND `option_name` NOT IN (\'bkntc_access_token\',\'bkntc_purchase_code\', \'bkntc_plugin_version\',\'bkntc_transient_cache_booknetic\')');

		// Restore options...
		if( is_array( $options ) && !empty( $options ) )
		{
			foreach ( $options AS $option )
			{
				if( !is_array( $option ) || !isset($option['option_name']) || in_array( $option['option_name'], ['bkntc_access_token','bkntc_purchase_code', 'bkntc_plugin_version'] ) )
					continue;

				DB::DB()->insert( DB::DB()->base_prefix . 'options', $option );
			}
		}

		// Restore data of tables
		if( is_array( $tables ) && !empty( $tables ) )
		{
			foreach ( $tables AS $tableName => $tableData )
			{
				if( !is_array( $tableData ) )
					continue;

				foreach ( $tableData AS $row )
				{
					if( !is_array( $row ) )
						continue;

					DB::DB()->insert( DB::table( $tableName ), $row );
				}
			}
		}

		DB::DB()->query("SET FOREIGN_KEY_CHECKS = 1;");
		DB::DB()->query("COMMIT");
        DB::DB()->query("set autocommit = 1");

    }

	private static function rmDir( $dir )
	{
		$files = scandir( $dir );
		foreach ( $files as $filename )
		{
			if ( $filename == '.' || $filename == '..' )
				continue;

			if( is_dir( $dir . '/' . $filename ) )
			{
				self::rmDir( $dir . '/' . $filename );
			}
			else
			{
				unlink( $dir . '/' . $filename );
			}
		}

		rmdir( $dir );
	}

	private static function allowedFileTypes()
	{
		$allowedFormats = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'zip', 'rar', 'csv'];

		return Helper::secureFileFormats( $allowedFormats ) ;
	}

}