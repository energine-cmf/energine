<?php
    /**
     * Урра! Печеньки! :)
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007
     * @version $Id: index.php,v 1.17 2007/11/26 14:11:07 tigrenok Exp $
     */

    /**
     * Принудительная кодировка
     *
     */
	define('FORCED_CHARSET','utf8');
    define('ENERGINE_LABEL_FILENAME', '.htenergine');
    ini_set('display_errors',1);
    @set_time_limit(600);
    header('Content-Type: text/html; charset='.FORCED_CHARSET);

    if(version_compare(PHP_VERSION, '5.3', '<')){
        die('Ваша версия PHP ниже необходимой для работы Energine. Ваша версия: '.PHP_VERSION.', необходимая версия: 5.3');
    }

    require_once('classes/Viewer.class.php');
    require_once('classes/ServerChecker.class.php');
    require_once('classes/DataChecker.class.php');
    require_once('classes/SQLDumper.class.php');
    require_once('classes/Processor.class.php');
    require_once('classes/Linker.class.php');
    require_once('classes/ViewOptions.class.php');
    require_once('classes/CheckerException.class.php');
    require_once('classes/SystemConfig.class.php');

    /**
     * Имя скрипта. Для определения корня сайта.
     *
     */
    define('SCRIPT_NAME','/setup/index.php');

    /**
     * Путь к файлу конфигурации (относительно htdocs)
     *
     */
    define('PATH_SYSTEM_CONFIG','site/system.config.php');

    /**
     * Путь к файлу дампа
     *
     */
    define('PATH_SQL_DUMP','data/energine_db_dump.sql');

    /**
     * Права, которые система будет устанавливать для директорий
     *
     */
    define('CHMOD_DIRS', 0755);

    /**
     * Права, которые система будет устанавливать для файлов
     *
     */
    define('CHMOD_FILES', 0644);
    
    define('UPLOADS_CHMOD_DIRS', 0777);
    
    define('UPLOADS_FOLDER_NAME', 'uploads');

    $viewer = new Viewer();

    $state = (isset($_GET['state'])) ? $_GET['state'] : null;




/**
*
* Тут надо проверить режим отладки
*
*/

    $systemConfig = include_once('../'.PATH_SYSTEM_CONFIG);

    if(!$systemConfig['site']['debug']) {
        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
        $viewer->addBlock('Error 403. Режим отладки отключён! Доступ к разделу вне режима отладки - запрещён!',Viewer::TPL_ERROR);
        $viewer->printResult();
        die;
    }



    try {

        switch ($state) {
            case 'checkserver':
                $checker = new ServerChecker();
                $checker->setViewer($viewer);
                $checker->silentMode(false);
                $checker->run();
                break;
            case 'install':
                $checker = new ServerChecker();
                $checker->setViewer($viewer);
                $checker->run();

                $datachecker = new DataChecker($Config);
                $datachecker->setViewer($viewer);
                $datachecker->run(Viewer::TPL_FORM);

                $data = $datachecker->getData();
                if (!empty($data)) {
                	$proc = new Processor($data,$Config);
                	$proc->setViewer($viewer);
                	$proc->run();

                	$sql = new SQLDumper($data,$Config);
                	$sql->setViewer($viewer);
                	$sql->run();

                	$linker = new Linker($data);
                	$linker->setViewer($viewer);
                	$linker->run();
                }
                break;
            case 'sqlrestore':
                $checker = new ServerChecker();
                $checker->setViewer($viewer);
                $checker->run();

                $datachecker = new DataChecker($Config);
                $datachecker->setViewer($viewer);
                $datachecker->run(Viewer::TPL_SQLFORM);

                $data = $datachecker->getData();
                if (!empty($data)) {
                	$sql = new SQLDumper($data,$Config);
                	$sql->setViewer($viewer);
                	$sql->run();
                }
                break;
            case 'sqldump':
                $datachecker = new DataChecker($Config);
                $datachecker->setViewer($viewer);
                $datachecker->run(Viewer::TPL_SQLDFORM);

                $data = $datachecker->getData();
                if (!empty($data)) {
                	$sql = new SQLDumper($data,$Config);
                	$sql->setViewer($viewer);
                	$sql->run();
                }
                break;
            case 'addadmin':
                $datachecker = new DataChecker($Config);
                $datachecker->setViewer($viewer);
                $datachecker->run(Viewer::TPL_USERFORM);

                $data = $datachecker->getData();
                if (!empty($data)) {
                	$sql = new SQLDumper($data,$Config);
                	$sql->setViewer($viewer);
                	$sql->run();
                }
                break;
            case 'linker':
                $checker = new ServerChecker();
                $checker->setViewer($viewer);
                $checker->run();

                $linker = new Linker();
            	$linker->setViewer($viewer);
            	$linker->run();
                break;
            default:
                $options = new ViewOptions();
                $options->setViewer($viewer);
                $options->run();
        }

    } catch (CheckerException $e) {
        if ($e->getMessage()) {
        	$viewer->addBlock($e->getMessage(),$e->getTPL());
        }
    } catch (Exception $e) {
        $viewer->addBlock($e->getMessage(),Viewer::TPL_ERROR);
    }


    $viewer->printResult();