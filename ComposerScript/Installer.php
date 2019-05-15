<?php

namespace ComposerScript;

use Composer\Script\Event;

class Installer
{

    public static function postInstall(Event $event)
    {
    	/*if (file_exists('simplesamlphp')) {
			self::rm_r('simplesamlphp');
		}*/
        self::configureSimpleSAMLphp();
    }

    public static function postUpdate(Event $event)
    {
        self::configureSimpleSAMLphp();
    }

    public static function updateSimpleSAMLphp(){
    	self::copy_r("./vendor/simplesamlphp/simplesamlphp", "./simplesamlphp/");
    }

    private static function configureSimpleSAMLphp()
    {
    	

    	//shell_exec('composer create-project composer create-project --prefer-dist --stability=dev simplesamlphp/simplesamlphp:dev-Xnew-ui');
		//self::rm_r('vendor');
		
		
		//self::copy_r("./vendor/simplesamlphp/simplesamlphp", "./simplesamlphp/");

		$dateString = date("YmdHis");

		$sspDir = 'simplesamlphp'.$dateString;

		$configDir = "ssp-config";

		//if (!file_exists('simplesamlphp')) {
			//mkdir($sspDir);
		rename('./vendor/simplesamlphp/simplesamlphp','./'.$sspDir);
		rename('./vendor','./'.$sspDir.'/vendor');
		if (!file_exists($configDir)) {
			mkdir($configDir);
		}
		//}

		//exec('\cp -r ./vendor/simplesamlphp/simplesamlphp/* ./'.$sspDir);

		if (!file_exists($configDir.'/cert')) {
			mkdir($configDir.'/cert');
		}

		if (!file_exists($configDir.'/config')) {
			mkdir($configDir.'/config');
		}

		if (!file_exists($configDir.'/metadata')) {
			mkdir($configDir.'/metadata');
		}

		if (!file_exists($sspDir.'/cache')) {
			mkdir($sspDir.'/cache');
		}

		if (!file_exists($sspDir.'/datadir')) {
			mkdir($sspDir.'/datadir');
		}

		if (!file_exists($sspDir.'/log')) {
			mkdir($sspDir.'/log');
		}


		$windows_os = array("WIN32","WINNT","Windows");

        if(!in_array(PHP_OS, $windows_os)){
            $apacheUser = exec('grep "User " `find /etc/ -name httpd.conf` | cut -d " " -f 2');
			$apacheGroup = exec('grep "Group " `find /etc/ -name httpd.conf` | cut -d " " -f 2');
        }

		
		$filePermissions = octdec("0664");
		$folderPermissions = octdec("0775");

		if (!file_exists($configDir."/metadata/saml20-idp-hosted.php")) {
			copy($sspDir."/metadata-templates/saml20-idp-hosted.php", $configDir."/metadata/saml20-idp-hosted.php");
		}
		
		if (!file_exists($configDir."/metadata/saml20-idp-remote.php")) {
			copy($sspDir."/metadata-templates/saml20-idp-remote.php", $configDir."/metadata/saml20-idp-remote.php");
		}
		if (!file_exists($configDir."/metadata/saml20-sp-remote.php")) {
			copy($sspDir."/metadata-templates/saml20-sp-remote.php", $configDir."/metadata/saml20-sp-remote.php");
		}
		if (!file_exists($configDir."/config/acl.php")) {
			copy($sspDir."/config-templates/acl.php", $configDir."/config/acl.php");
		}
		if (!file_exists($configDir."/config/authmemcookie.php")) {
			copy($sspDir."/config-templates/authmemcookie.php", $configDir."/config/authmemcookie.php");
		}
		if (!file_exists($configDir."/config/authsources.php")) {
			copy($sspDir."/config-templates/authsources.php", $configDir."/config/authsources.php");
		}
		if (!file_exists($configDir."/config/config.php")) {
			copy($sspDir."/config-templates/config.php", $configDir."/config/config.php");
			self::downloadAndWriteConfig($configDir."/config/config.php");
		}
		if (!file_exists($configDir."/config/updater_config.php")) {
			copy($sspDir."/modules/updater/config_template/updater_config.php", $configDir."/config/updater_config.php");
		}
		//self::copy_r("modules/idpinstaller", "simplesamlphp/modules/idpinstaller");
		//self::copy_r("modules/hubandspoke", "simplesamlphp/modules/hubandspoke");
		//self::copy_r("modules/sir2skin", "simplesamlphp/modules/sir2skin");
		//self::rm_r('modules');

		if (file_exists($sspDir.'/metadata')) {
			self::rm_r($sspDir.'/metadata');
		}

		if (file_exists($sspDir.'/cert')) {
			self::rm_r($sspDir.'/cert');
		}

		if (file_exists($sspDir.'/config')) {
			self::rm_r($sspDir.'/config');
		}

		symlink (realpath($configDir."/metadata/"), $sspDir."/metadata");
		symlink (realpath($configDir."/cert/"), $sspDir."/cert");
		symlink (realpath($configDir."/config/"), $sspDir."/config");

		chmod($configDir."/metadata/saml20-idp-hosted.php", $filePermissions);
		chmod($configDir."/metadata/saml20-sp-remote.php", $filePermissions);

		self::chmod_r($sspDir."/modules", $folderPermissions);


		if (file_exists($sspDir.'/modules/hubandspoke/default-disable')) {
			rename($sspDir.'/modules/hubandspoke/default-disable',$sspDir.'/modules/hubandspoke/default-enable');
		}else if(!file_exists($sspDir.'/modules/hubandspoke/default-enable')){
			touch($sspDir.'/modules/hubandspoke/default-enable');
		}

		if (file_exists($sspDir.'/modules/exampleauth/default-disable')) {
			unlink($sspDir.'/modules/exampleauth/default-disable');
		}else if(!file_exists($sspDir.'/modules/exampleauth/default-enable')){
			touch($sspDir.'/modules/exampleauth/default-enable');
		}

		if (file_exists($sspDir.'/modules/sir2skin/default-disable')) {
			rename($sspDir.'/modules/sir2skin/default-disable',$sspDir.'/modules/sir2skin/default-enable');
		}else if(!file_exists($sspDir.'/modules/sir2skin/default-enable')){
			touch($sspDir.'/modules/sir2skin/default-enable');
		}

		if (file_exists($sspDir.'/modules/updater/default-disable')) {
			rename($sspDir.'/modules/updater/default-disable',$sspDir.'/modules/updater/default-enable');
		}else if(!file_exists($sspDir.'/modules/updater/default-enable')){
			touch($sspDir.'/modules/updater/default-enable');
		}

		chmod($configDir."/config/config.php", $filePermissions);
		chmod($sspDir."/modules/idpinstaller/lib/makeCert.sh", $folderPermissions);

		self::chmod_r($configDir."/cert", $folderPermissions);
		if(!in_array(PHP_OS, $windows_os)){
			chown('composer.json', $apacheUser);
			chgrp('composer.json', $apacheGroup);
			self::chown_r($sspDir, $apacheUser, $apacheGroup);
			self::chown_r($configDir, $apacheUser, $apacheGroup);
		}

		/*if(file_exists("simplesamlphp")){
			touch("llega3.txt");
			exec("rm simplesamlphp");
		}*/

		if(file_exists("simplesamlphp")){
			$currentLinkPath = readlink(realpath("simplesamlphp"));
			$currentLinkPath = str_replace('\\', '/', $currentLinkPath);
			$partsCurrentLinkPath = explode("/", $currentLinkPath);
			rename("simplesamlphp", "link_".$partsCurrentLinkPath[count($partsCurrentLinkPath)-1]);
		}

		symlink(realpath($sspDir) ,"simplesamlphp");	

		if(file_exists("composer.back.json")){
			rename('composer.back.json', 'composer.json');
		}

		return true;
    }

    private static function downloadAndWriteConfig($configPath)
    {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://www.rediris.es/sir2/IdP/install/config.php.txt");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);
		
		curl_close ($ch);

		file_put_contents($configPath, $result);

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
