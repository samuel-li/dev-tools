<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*
* Copyright 2020
* samuel.li@cn.ibm.com
* Usage: cd SUGARCRM; php repair.php
*/

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use Sugarcrm\Sugarcrm\Console\CommandRegistry\Mode\InstanceModeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RepairAndClear;
use LanguageManager;

ini_set('display_errors',1);
error_reporting(E_ALL);

if(!defined('sugarEntry'))define('sugarEntry', true);

require __DIR__ . '/vendor/autoload.php';

require_once('include/utils.php');
require_once 'include/dir_inc.php';

require_once 'include/utils/array_utils.php';
require_once 'include/utils/file_utils.php';
require_once 'include/utils/security_utils.php';
require_once 'include/utils/logic_utils.php';
require_once 'include/utils/sugar_file_utils.php';
require_once 'include/utils/mvc_utils.php';
require_once 'include/utils/db_utils.php';
require_once 'include/utils/encryption_utils.php';


$dir = "./custom/modules/";

$logicHooks = array();
$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
generateCSS();

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            $fileType = filetype($dir . $file);
            if ($fileType == "dir" && $file != "." && $file != ".." ) {
                $targetFile = LookupFile($dir . $file . "/", "LogicHooks/logichooks.ext.php");
                if ($targetFile != null) {
                    $logicHooks[$file] = $targetFile;
                }
            }
        }
        closedir($dh);
    }

    $modules = array();

    if (count($logicHooks) > 0) {
        ksort($logicHooks);
        foreach ($logicHooks as $module=>$hookFile) {
            $modules[] = $module;
            $sortedHooks = SortHooksByPriority($hookFile);
            if (!empty($sortedHooks)) {
                generateHooksIndex($module, $sortedHooks);
            }
        }
    }
    generateIndex($modules);
}

function LookupFile($dir, $file) {
    if (file_exists($dir."Ext/".$file)) {
      return $dir."Ext/".$file;
    }
    return null;
}

function SortHooksByPriority($hookFile) {

    if (file_exists($hookFile)) {
        require $hookFile;
        if (!empty($hook_array)) {
            $hook_array_sorted = array();

            foreach($hook_array as $trigger=>$actions) {
                foreach($actions as $_act) {
                    $hook_array_sorted[$trigger][$_act[0]][]= $_act;
                }
            }

            foreach($hook_array_sorted as $trigger=>$actions) {
                ksort($hook_array_sorted[$trigger]);
            }
            krsort($hook_array_sorted);
            return $hook_array_sorted;
        }
        return null;
    }
}

function generateHooksIndex($module, $sortedHooks) {
    $idxDir = "HooksRef";

    if (!file_exists($idxDir)) {
        mkdir($idxDir);
    }
    
    $idxFile = "{$idxDir}/{$module}_hooks.html";
    $fp = fopen($idxFile, "w");
    fputs($fp, "<html><head><title>{$module} Hooks</title></head>");
    fputs($fp, '<link rel="stylesheet" type="text/css" href="style.css" media="screen">');
    fputs($fp, "<body>");
    fputs($fp, "<a href='index.html'>Index</a><br />");
    foreach($sortedHooks as $trigger=>$actions) {
        //echo $trigger."\n";
        fputs($fp, "<div class='trigger'>{$trigger}");
        fputs($fp, "<div class='actions'>");
        foreach($actions as $priority => $_acts) {
            foreach($_acts as $_act) {
                //echo $_act[3].":".$_act[4]."\n";
                fputs($fp, "<div class='actsec'>");
                fputs($fp, "<span class='priority'>Priority : {$priority}</span>&nbsp; => &nbsp;");
                fputs($fp, "<span class='act'><a href='../".$_act[2]."'>".$_act[3].":".$_act[4]."</a>&nbsp;&nbsp;($_act[2])</span>");
                fputs($fp, "</div>");
            }
        }
        fputs($fp, "</div></div>");
    }
    fputs($fp, "<a href='index.html'>Index</a><br />");
    fputs($fp, "</body></html>");
}

function generateCSS() {
    $idxDir = "HooksRef";

    if (!file_exists($idxDir)) {
        mkdir($idxDir);
    }

    $fp = fopen($idxDir."/style.css", "w");
    fputs($fp, "
    .trigger{margin: 20px; font-size:24px;}
    .actions{padding: 10px; font-size:12px;}
    .actsec{margin-top: 10px;}
    .priority{padding-right: 5px;}
    .act{}
    ");
    fclose($fp);
}

function generateIndex($modules) {
    $idxDir = "HooksRef";

    if (!file_exists($idxDir)) {
        mkdir($idxDir);
    }
    
    $idxFile = "{$idxDir}/index.html";
    $fp = fopen($idxFile, "w");
    fputs($fp, "<html><head><title>Atlas Hooks</title></head>");
    fputs($fp, '<link rel="stylesheet" type="text/css" href="style.css" media="screen">');
    fputs($fp, "<body>");
    foreach($modules as $module) {
        fputs($fp, "<a href='{$module}_hooks.html'>{$module}</a><br />");
    }
    fputs($fp, "</body></html>");
    fclose($fp);
}