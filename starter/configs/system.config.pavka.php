<?php

/**
 * Конфигурация проекта на базе системы управления сайтами Energine
 *
 * @copyright 2013 Energine
 */
return array(

    // название проекта
    'project' => 'Energine master',

    // путь к директории setup текущего используемого ядра
    'setup_dir' => ($energine_release = '/home/pavka/energine.git/master') . '/setup',

    // список подключенных модулей ядра в конкретном проекте
    // ключи массива - названия модулей, значения - абсолютные пути к месторасположению
    'modules' => array(
        'share'     => $energine_release . '/core/modules/share',
        'user'      => $energine_release . '/core/modules/user',
        'apps'      => $energine_release . '/core/modules/apps',
        'forms'     => $energine_release . '/core/modules/forms',
        'seo'       => $energine_release . '/core/modules/seo',
        'calendar'  => $energine_release . '/core/modules/calendar',
        'comments'  => $energine_release . '/core/modules/comments',
    ),

    // настройки подключения к mysql
    'database' => array(
        'host' => 'dbhost',
        'port' => '3306',
        'db' => 'pavka_energine',
        'username' => 'pavka',
        'password' => '0per.du'
    ),

    // настройки сайта
    'site' => array(
        // имя домена
        'domain' => 'starter.pavka.eggmen.net',
        // корень проекта
        'root' => '/',
        // отладочный режим: 1 - включено, 0 - выключено
        'debug' => 1,
        // делать ли замеры времени рендеринга страниц и выводить их в header X-Timer
        'useTimer' => 1,
        // выводить для отладки сразу в XML
        'asXML' => 0,
        // использовать Tidy для очистки кода текстового блока от лишних тегов (если модяль Tidy не подключен  - работать не будет )
        'aggressive_cleanup' => 1,
        // перечень глобальных переменных, которые будут доступны в XML документе на всех страницах
        /*
        'vars' => array(
            'SOME_GLOBAL_XML_VARIABLE' => 'some constant value',
            'ANOTHER_GLOBAL_XML_VARIABLE' => 'another value',
        ),
        */
    ),
    // настройки документа
    'document' => array(
        // основная точка входа в xslt преобразователь
        'transformer' => 'main.xslt',
        // насткойка кеширования xslt (при использовании XSLTCache)
        'xslcache' => 0,
    ),

    // перечень дополнительнх полей с превьюшками в виде отдельной вкладки в файловом менеджере
    'thumbnails' => array(
        'auxmiddle' => array(
            'width' => 190,
            'height' => 132,
        ),
        'middle' => array(
            'width' => 184,
            'height' => 138,
        ),
        'anchormiddle' => array(
            'width' => 190,
            'height' => 192,
        ),
        'anchorxsmall' => array(
            'width' => 48,
            'height' => 48,
        ),
        'small' => array(
            'width' => 140,
            'height' => 107,
        ),
        'xsmall' => array(
            'width' => 75,
            'height' => 56,
        ),
        'xxsmall' => array(
            'width' => 60,
            'height' => 45,
        ),
        'big' => array(
            'width' => 650,
            'height' => 367,
        ),
    ),
    // дополнительные внешние системы авторизации
    /*'auth' => array(
        // VK.COM
        'vk' => array(
            'appID' => 'VK APP ID',
            'secretKey' => 'VK SECRET'
        ),
        // FACEBOOK.COM
        'facebook' => array(
            'appID' => 'FACEBOOK APP ID',
            'secretKey' => 'FACEBOOK SECRET'
        ),
    ),*/

    // натройка сессий
    'session' => array(
        'timeout' => 6000,
        'lifespan' => 108000,
    ),

    // настройка почтовых уведомлений
    'mail' => array(
        // адрес отправителя почтовой корреспонденции
        'from' => 'noreply@energine.org',
        // адрес менеджера
        'manager' => 'demo@energine.org',
        // адрес для сообщений обратной связи
        'feedback' => 'demo@energine.org'
    ),

    // настройки google
    'google' => array(
        'verify' => '',
        'analytics' => ''
    ),

    // настройки recaptcha
    'recaptcha' => array(
        'public' => '6LfkCeASAAAAALl-av9HM_RG1AU-tcta3teX7Z2u',
        'private' => '6LfkCeASAAAAABPo4F3GoXULR2w5EgHjjd3RDjXk'
    ),

    // настройки файловых репозитариев
    'repositories' => array(
        // маппинг типов репозитариев (share_uploads.upl_mime_type) с реализациями интерфейса IFileRepository
        'mapping' => array(
            'repo/local' => 'FileRepositoryLocal',
            'repo/ftp' => 'FileRepositoryFTP',
            'repo/ftpro' => 'FileRepositoryFTPRO',
            'repo/ro' => 'FileRepositoryRO',
        ),
        // папка по-умолчанию для быстрой загрузки файлов
        'quick_upload_path' => 'uploads/public',
        // конфигурация для FTP репозитариев
        'ftp' => array(
            // конфигурация FTP доступа для репозитария с share_uploads.upl_path uploads/ftp
            'uploads/ftp' => array(
                'media' => array(
                    'server' => '10.0.1.10',
                    'port' => 21,
                    'username' => 'username',
                    'password' => 'password'
                ),
                'alts' => array(
                    'server' => '10.0.1.10',
                    'port' => 21,
                    'username' => 'username',
                    'password' => 'password',
                )
            ),
            // конфигурация FTP доступа для репозитария с share_uploads.upl_path uploads/ftpro
            'uploads/ftpro' => array(
                'media' => array(
                    'server' => '10.0.1.10',
                    'port' => 21,
                    'username' => 'username',
                    'password' => 'password'
                ),
                'alts' => array(
                    'server' => '10.0.1.10',
                    'port' => 21,
                    'username' => 'username',
                    'password' => 'password',
                )
            )
        ),
    ),

    // настройка SEO модуля
    'seo' => array(
        'sitemapSegment' => 'google_sitemap',
        'sitemapTemplate' => 'google_sitemap',
        'maxVideosInMap' => '10'
    ),

    // параметри пользовательских стилей RichText редактора
    'wysiwyg' => array(
        'styles' => array(
            'p.red' => array(
                'element' => 'p',
                'class' => 'red',
                'caption' => 'TXT_RED_PARAGRAPH'
            ),
            'p.underline' => array(
                'element' => 'p',
                'class' => 'underline',
                'caption' => 'TXT_TEXT_UNDERLINE'
            )
        )
    ),

);

