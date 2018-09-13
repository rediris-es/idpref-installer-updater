<?php

namespace ComposerScript;

use Composer\Script\Event;

class Installer
{

    public static function postInstall(Event $event)
    {
        self::configureSimpleSAMLphp();
    }

    public static function postUpdate(Event $event)
    {
        self::configureSimpleSAMLphp();
    }

    private static function configureSimpleSAMLphp()
    {
    	/*if (file_exists('simplesamlphp')) {
			self::rm_r('simplesamlphp');
		}*/

    	//shell_exec('composer create-project composer create-project --prefer-dist --stability=dev simplesamlphp/simplesamlphp:dev-Xnew-ui');
		//self::rm_r('vendor');
		//rename('./vendor/simplesamlphp/simplesamlphp','./simplesamlphp');
		//rename('./vendor','./simplesamlphp/vendor');
		
		if (!file_exists('simplesamlphp/cert')) {
			mkdir('simplesamlphp/cert');
		}

		if (!file_exists('simplesamlphp/config')) {
			mkdir('simplesamlphp/config');
		}

		if (!file_exists('simplesamlphp/metadata')) {
			mkdir('simplesamlphp/metadata');
		}

		if (!file_exists('simplesamlphp/cache')) {
			mkdir('simplesamlphp/cache');
		}

		$apacheUser = exec('ps axo user | grep apache | grep -v root | uniq');
		$apacheGroup = exec('ps axo group | grep apache | grep -v root | uniq');
		$filePermissions = octdec("0664");
		$folderPermissions = octdec("0775");

		copy("simplesamlphp/metadata-templates/saml20-idp-hosted.php", "simplesamlphp/metadata/saml20-idp-hosted.php");
		copy("simplesamlphp/metadata-templates/saml20-idp-remote.php", "simplesamlphp/metadata/saml20-idp-remote.php");
		copy("simplesamlphp/metadata-templates/saml20-sp-remote.php", "simplesamlphp/metadata/saml20-sp-remote.php");
		copy("simplesamlphp/config-templates/acl.php", "simplesamlphp/config/acl.php");
		copy("simplesamlphp/config-templates/authmemcookie.php", "simplesamlphp/config/authmemcookie.php");
		copy("simplesamlphp/config-templates/authsources.php", "simplesamlphp/config/authsources.php");
		copy("simplesamlphp/config-templates/config.php", "simplesamlphp/config/config.php");
		chmod("simplesamlphp/metadata/saml20-idp-hosted.php", $filePermissions);
		chmod("simplesamlphp/metadata/saml20-sp-remote.php", $filePermissions);
		//self::copy_r("modules/idpinstaller", "simplesamlphp/modules/idpinstaller");
		//self::copy_r("modules/hubandspoke", "simplesamlphp/modules/hubandspoke");
		//self::copy_r("modules/sir2skin", "simplesamlphp/modules/sir2skin");
		//self::rm_r('modules');
		self::chmod_r("simplesamlphp/modules", $folderPermissions);

		if (file_exists('simplesamlphp/modules/hubandspoke/default-disable')) {
			rename('simplesamlphp/modules/hubandspoke/default-disable','simplesamlphp/modules/hubandspoke/default-enable');
		}

		if (file_exists('simplesamlphp/modules/exampleauth/default-disable')) {
			unlink('simplesamlphp/modules/exampleauth/default-disable');
		}

		touch('simplesamlphp/modules/exampleauth/enable');
		touch('simplesamlphp/modules/sir2skin/default-enable');
		
		if (file_exists('simplesamlphp/modules/sir2skin/default-disable')) {
			rename('simplesamlphp/modules/sir2skin/default-disable','simplesamlphp/modules/sir2skin/default-enable');
		}

		self::downloadAndWriteConfig();
		chmod("simplesamlphp/config/config.php", $filePermissions);
		chmod("simplesamlphp/modules/idpinstaller/lib/makeCert.sh", $folderPermissions);

		if (file_exists('simplesamlphp/modules/sir2skin/default.disable')) {
			rename('simplesamlphp/modules/sir2skin/default.disable','simplesamlphp/modules/sir2skin/default-enable');
		}
		
		self::chmod_r("simplesamlphp/cert", $folderPermissions);
		self::chown_r('simplesamlphp', $apacheUser, $apacheGroup);
    }

    private static function downloadAndWriteConfig()
    {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://www.rediris.es/sir2/IdP/install/config.php.txt");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);
		
		curl_close ($ch);

		file_put_contents('simplesamlphp/config/config.php', $result);

    }

    private static function chmod_r($path, $filemode) 
    {
	    chmod($path, $filemode);

	    $d = opendir($path);

	    while (($file = readdir($d)) !== false) {
	        if($file != '.' && $file != '..') {
		        $typepath = $path.'/'.$file;

		        if (filetype ($typepath) == 'dir') {
	                self::chmod_r($typepath, $filemode);
	            }
	            chmod($typepath, $filemode);
	        }
	    }

	    closedir($d);

	}


    private static function chown_r($path, $uid, $gid)
	{
		chown($path, $uid);
		chgrp($path, $gid);

	    $d = opendir ($path) ;
	    
	    while(($file = readdir($d)) !== false) {
	        if ($file != "." && $file != "..") {

	            $typepath = $path . "/" . $file ;

	            if (filetype ($typepath) == 'dir') {
	                self::chown_r($typepath, $uid, $gid);
	            }

	            chown($typepath, $uid);
	            chgrp($typepath, $gid);

	        }
	    }

	    closedir($d);

	}


    private static function rm_r($src) 
    {
	    $dir = opendir($src);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            $full = $src . '/' . $file;
	            if ( is_dir($full) ) {
	                self::rm_r($full);
	            }
	            else {
	                unlink($full);
	            }
	        }
	    }
	    closedir($dir);
	    rmdir($src);
	}


	private static function copy_r($src,$dst) 
	{
	    $dir = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir($src . '/' . $file) ) {
	                self::copy_r($src . '/' . $file,$dst . '/' . $file);
	            }
	            else {
	                copy($src . '/' . $file,$dst . '/' . $file);
	            }
	        }
	    }
	    closedir($dir);
	}
}

?>