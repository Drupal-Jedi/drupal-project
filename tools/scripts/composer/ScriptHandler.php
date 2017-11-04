<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use Drupal\Component\Utility\Crypt;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler {

  public static function createRequiredFiles(Event $event) {
    $fs = new Filesystem();
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $drupalRoot = $drupalFinder->getDrupalRoot();

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing.
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupalRoot . '/'. $dir)) {
        $fs->mkdir($drupalRoot . '/'. $dir);
        $fs->touch($drupalRoot . '/'. $dir . '/.gitkeep');
      }
    }

    // Prepare the settings.php file for installation.
    if (!$fs->exists($drupalRoot . '/sites/default/settings.php')) {
      $fs->touch($drupalRoot . '/sites/default/settings.php');

      $defaultSeetings = self::settingsFile();
      file_put_contents($drupalRoot . '/sites/default/settings.php', $defaultSeetings);

      $fs->chmod($drupalRoot . '/sites/default/settings.php', 0644);
      $event->getIO()->write("Create a sites/default/settings.php file with chmod 0644");
    }

    // Prepare the prj-settings.inc file for installation.
    if (!$fs->exists($drupalRoot . '/sites/prj-settings.inc')) {
      $fs->touch($drupalRoot . '/sites/prj-settings.inc');

      $defaultPrjSettings = self::prjSettingsFile();
      file_put_contents($drupalRoot . '/sites/prj-settings.inc', $defaultPrjSettings);

      $fs->chmod($drupalRoot . '/sites/prj-settings.inc', 0644);
      $event->getIO()->write("Create a sites/prj-settings.inc file with chmod 0644");
    }

    // Create the files directory with chmod 0777.
    if (!$fs->exists($drupalRoot . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($drupalRoot . '/sites/default/files', 0755);
      umask($oldmask);
      $event->getIO()->write("Create a sites/default/files directory with chmod 0755");
    }
  }

  public static function settingsFile() {
    $defaultSetting[] = '<?php';
    $defaultSetting[] = '';
    $defaultSetting[] = '/**';
    $defaultSetting[] = ' * @file';
    $defaultSetting[] = ' * Custom single site-specific settings.';
    $defaultSetting[] = ' *';
    $defaultSetting[] = ' * @see prj-settings.inc for default settings.';
    $defaultSetting[] = ' */';
    $defaultSetting[] = '';
    $defaultSetting[] = '// It\'s not in the common settings file because for multi-site we need';
    $defaultSetting[] = '// to specify different settings, i.e. DB connection.';
    $defaultSetting[] = '$site_settings_filename = \'project_name-settings.inc\';';
    $defaultSetting[] = '';
    $defaultSetting[] = 'require_once DRUPAL_ROOT . \'/sites/prj-settings.inc\';';
    $defaultSetting[] = '';

    return implode(PHP_EOL, $defaultSetting);
  }

  public static function prjSettingsFile() {
    $defaultPrjSettings[] = '<?php';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '/**';
    $defaultPrjSettings[] = ' * @file';
    $defaultPrjSettings[] = ' * Custom settings common for all multi-sites of the project.';
    $defaultPrjSettings[] = ' */';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = 'use Drupal\Component\Assertion\Handle;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '$databases = [];';
    $defaultPrjSettings[] = '$config_directories = [];';
    $defaultPrjSettings[] = '$settings[\'hash_salt\'] = \'' . Crypt::randomBytesBase64(55) . '\';';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '$settings[\'update_free_access\'] = FALSE;';
    $defaultPrjSettings[] = '// @codingStandardsIgnoreStart';
    $defaultPrjSettings[] = '$settings[\'container_yamls\'][] = $app_root . \'/\' . $site_path . \'/services.yml\';';
    $defaultPrjSettings[] = '// @codingStandardsIgnoreEnd';
    $defaultPrjSettings[] = '$settings[\'file_scan_ignore_directories\'] = [';
    $defaultPrjSettings[] = '  \'node_modules\',';
    $defaultPrjSettings[] = '  \'bower_components\',';
    $defaultPrjSettings[] = '];';
    $defaultPrjSettings[] = '$settings[\'install_profile\'] = \'standard\';';
    $defaultPrjSettings[] = '$config_directories[CONFIG_SYNC_DIRECTORY] = \'../config\';';
    $defaultPrjSettings[] = '$settings[\'file_private_path\'] = \'sites/default/files/private\';';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '// Include env settings file for certain site';
    $defaultPrjSettings[] = '// (because DB could be different and etc).';
    $defaultPrjSettings[] = '// Structure of settings folder — folder per project';
    $defaultPrjSettings[] = '// and per site settings file inside.';
    $defaultPrjSettings[] = '$env_settings_path = \'/var/www/site-php/project_name\';';
    $defaultPrjSettings[] = '// If PROJECT_ENV_MULTI is TRUE,';
    $defaultPrjSettings[] = '// it means we have several envs on the same machine.';
    $defaultPrjSettings[] = '// I.e. same real instance for dev and stage. Or stage and prod.';
    $defaultPrjSettings[] = '// It means we need to include file per env type.';
    $defaultPrjSettings[] = 'if (!empty($_ENV[\'PROJECT_ENV_MULTI\']) && !empty($_ENV[\'PROJECT_ENV_TYPE\'])) {';
    $defaultPrjSettings[] = '  $env_settings_path .= \'-\' . $_ENV[\'PROJECT_ENV_TYPE\'];';
    $defaultPrjSettings[] = '}';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '// @codingStandardsIgnoreStart';
    $defaultPrjSettings[] = 'if (is_readable($env_settings_path) && isset($site_settings_filename)) {';
    $defaultPrjSettings[] = '  require $env_settings_path . \'/\' . $site_settings_filename;';
    $defaultPrjSettings[] = '}';
    $defaultPrjSettings[] = '// @codingStandardsIgnoreEnd';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '// @TODO: This is temp a solution to make available Wodby constants within our settings file. Pray to Wodby.';
    $defaultPrjSettings[] = '// This should be removed once Wodby provide another way';
    $defaultPrjSettings[] = '// to make settings project dependent.';
    $defaultPrjSettings[] = 'elseif (is_readable(__DIR__ . \'/wodby.settings.php\')) {';
    $defaultPrjSettings[] = '  require_once __DIR__ . \'/wodby.settings.php\';';
    $defaultPrjSettings[] = '}';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = 'define(\'PROJECT_ENV_PROD\', \'prod\');';
    $defaultPrjSettings[] = 'define(\'PROJECT_ENV_STAGE\', \'stage\');';
    $defaultPrjSettings[] = 'define(\'PROJECT_ENV_DEV\', \'dev\');';
    $defaultPrjSettings[] = 'define(\'PROJECT_ENV_LOCAL\', \'local\');';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '/**';
    $defaultPrjSettings[] = ' * Return a name of environment we are deployed at.';
    $defaultPrjSettings[] = ' *';
    $defaultPrjSettings[] = ' * @return string';
    $defaultPrjSettings[] = ' *   Environment identification string.';
    $defaultPrjSettings[] = ' */';
    $defaultPrjSettings[] = 'function project_get_environment() {';
    $defaultPrjSettings[] = '  static $project_env = NULL;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '  if (is_null($project_env)) {';
    $defaultPrjSettings[] = '    // It\'s example of detection but could be used as is if hosting the same.\';';
    $defaultPrjSettings[] = '    // Is it a wodby hosting?';
    $defaultPrjSettings[] = '    if (defined(\'WODBY_ENVIRONMENT_TYPE\')) {';
    $defaultPrjSettings[] = '      $project_env = WODBY_ENVIRONMENT_TYPE;';
    $defaultPrjSettings[] = '    }';
    $defaultPrjSettings[] = '    // Is it an Acquia hosting?';
    $defaultPrjSettings[] = '    elseif (!empty($_ENV[\'AH_SITE_ENVIRONMENT\'])) {';
    $defaultPrjSettings[] = '      $acquia_env_mapping = array(';
    $defaultPrjSettings[] = '        \'test\' => PROJECT_ENV_STAGE,';
    $defaultPrjSettings[] = '      );';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '      $project_env = isset($acquia_env_mapping[$_ENV[\'AH_SITE_ENVIRONMENT\']])';
    $defaultPrjSettings[] = '        ? $acquia_env_mapping[$_ENV[\'AH_SITE_ENVIRONMENT\']]';
    $defaultPrjSettings[] = '        : $_ENV[\'AH_SITE_ENVIRONMENT\'];';
    $defaultPrjSettings[] = '    }';
    $defaultPrjSettings[] = '    // Self-hosted projects.';
    $defaultPrjSettings[] = '    elseif (getenv(\'PROJECT_ENV_TYPE\')) {';
    $defaultPrjSettings[] = '      $project_env = getenv(\'PROJECT_ENV_TYPE\');';
    $defaultPrjSettings[] = '    }';
    $defaultPrjSettings[] = '    // Default env is local.';
    $defaultPrjSettings[] = '    else {';
    $defaultPrjSettings[] = '      $project_env = PROJECT_ENV_LOCAL;';
    $defaultPrjSettings[] = '    }';
    $defaultPrjSettings[] = '  }';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '  return $project_env;';
    $defaultPrjSettings[] = '}';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '// For separate balancers we need to forward is original request was an HTTPS.';
    $defaultPrjSettings[] = 'if (isset($_SERVER[\'HTTP_X_FORWARDED_PROTO\']) && $_SERVER[\'HTTP_X_FORWARDED_PROTO\'] == \'https\') {';
    $defaultPrjSettings[] = '  $_SERVER[\'HTTPS\'] = \'on\';';
    $defaultPrjSettings[] = '}';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '// Environment-specific settings overrides.';
    $defaultPrjSettings[] = 'switch (project_get_environment()) {';
    $defaultPrjSettings[] = '  case PROJECT_ENV_PROD:';
    $defaultPrjSettings[] = '    $config[\'system.logging\'][\'error_level\'] = \'none\';';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'css\'][\'preprocess\'] = TRUE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'js\'][\'preprocess\'] = TRUE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'cache\'][\'page\'][\'max_age\'] = 86400;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '    // Decrease/increase the memory limit for PROD environment.';
    $defaultPrjSettings[] = '    ini_set(\'memory_limit\', \'256M\');';
    $defaultPrjSettings[] = '    break;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '  case PROJECT_ENV_STAGE:';
    $defaultPrjSettings[] = '    $config[\'system.logging\'][\'error_level\'] = \'verbose\';';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'css\'][\'preprocess\'] = TRUE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'js\'][\'preprocess\'] = TRUE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'cache\'][\'page\'][\'max_age\'] = 86400;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '    // Decrease/increase the memory limit for STAGE environment.';
    $defaultPrjSettings[] = '    ini_set(\'memory_limit\', \'256M\');';
    $defaultPrjSettings[] = '    break;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '  case PROJECT_ENV_DEV:';
    $defaultPrjSettings[] = '    assert_options(ASSERT_ACTIVE, TRUE);';
    $defaultPrjSettings[] = '    Handle::register();';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '    $config[\'system.logging\'][\'error_level\'] = \'verbose\';';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'css\'][\'preprocess\'] = TRUE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'js\'][\'preprocess\'] = TRUE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'cache\'][\'page\'][\'max_age\'] = 86400;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '    // Decrease/increase the memory limit for DEV environment.';
    $defaultPrjSettings[] = '    ini_set(\'memory_limit\', \'256M\');';
    $defaultPrjSettings[] = '    break;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '  case PROJECT_ENV_LOCAL:';
    $defaultPrjSettings[] = '    assert_options(ASSERT_ACTIVE, TRUE);';
    $defaultPrjSettings[] = '    Handle::register();';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '    $config[\'system.logging\'][\'error_level\'] = \'verbose\';';
    $defaultPrjSettings[] = '    $settings[\'container_yamls\'][] = DRUPAL_ROOT . \'/sites/development.services.yml\';';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'css\'][\'preprocess\'] = FALSE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'js\'][\'preprocess\'] = FALSE;';
    $defaultPrjSettings[] = '    $config[\'system.performance\'][\'cache\'][\'page\'][\'max_age\'] = 0;';
    $defaultPrjSettings[] = '    $settings[\'cache\'][\'bins\'][\'render\'] = \'cache.backend.null\';';
    $defaultPrjSettings[] = '    $settings[\'cache\'][\'bins\'][\'dynamic_page_cache\'] = \'cache.backend.null\';';
    $defaultPrjSettings[] = '    $config[\'advagg.settings\'][\'enabled\'] = FALSE;';
    $defaultPrjSettings[] = '';
    $defaultPrjSettings[] = '    // Allow any hosts for local usage.';
    $defaultPrjSettings[] = '    $settings[\'trusted_host_patterns\'] = [];';
    $defaultPrjSettings[] = '    break;';
    $defaultPrjSettings[] = '}';
    $defaultPrjSettings[] = '';

    return implode(PHP_EOL, $defaultPrjSettings);
  }

}
