<?php

/*
* Copyright 2013
* Jeff Bickart
* @bickart
* jeff @ neposystems.com
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
// require_once('include/entryPoint.php');
echo "step 1\n";
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

require_once 'include/SugarCache/SugarCache.php';


if (empty($GLOBALS['installing'])) {
    $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
}

if (!empty($sugar_config['xhprof_config'])) {
    SugarXHprof::getInstance()->start();
}

register_shutdown_function('sugar_cleanup');


require_once('sugar_version.php'); // provides $sugar_version, $sugar_db_version, $sugar_flavor

// Initialize InputValdation service as soon as possible. Up to this point
// it is expected that no code has altered any input superglobals.
InputValidation::initService();

// Check to see if custom utils exist and load them too
// not doing it in utils since autoloader is not loaded there yet
foreach (SugarAutoLoader::existing('include/custom_utils.php', 'custom/include/custom_utils.php', SugarAutoLoader::loadExtension('utils')) as $file) {
    require_once $file;
}

require_once('include/modules.php'); // provides $moduleList, $beanList, $beanFiles, $modInvisList, $adminOnlyList, $modInvisListActivities
require_once('modules/Administration/updater_utils.php');
require_once 'modules/Currencies/Currency.php';

UploadStream::register();

///////////////////////////////////////////////////////////////////////////////
////    Handle loading and instantiation of various Sugar* class
if (!defined('SUGAR_PATH')) {
    define('SUGAR_PATH', realpath(dirname(__FILE__) . '/..'));
}
require_once('modules/Administration/QuickRepairAndRebuild.php');

$GLOBALS['mod_strings'] = return_module_language('en_us', 'Administration');
$repair = new RepairAndClear();
$repair->repairAndClearAll(array('clearAll'), array(translate('LBL_ALL_MODULES')), true, false, '');
//remove the js language files
LanguageManager::removeJSLanguageFiles();
//remove language cache files
LanguageManager::clearLanguageCache();
