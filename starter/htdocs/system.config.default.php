<?php

/**
 * Конфигурация проекта на базе системы управления сайтами Energine
 *
 * @copyright 2013 Energine
 */
return array(

    // название проекта
    'project' => 'Platform 2.10',

    // список подключенных модулей ядра в конкретном проекте
    'modules' => array(
        'share',
        'user',
        'apps',
        'forms',
        'seo',
        'calendar'
    ),

    // настройки подключения к mysql
    'database' => array(
        'host' => 'localhost',
        'port' => '3306',
        'db' => 'DATABASE',
        'username' => 'USERNAME',
        'password' => 'PASSWORD'
    ),

    // настройки сайта
    'site' => array(
        // имя домена
        'domain' => 'energine.local',
        // корень проекта
        'root' => '/',
        // отладочный режим: 1 - включено, 0 - выключено
        'debug' => 1,
        'asXML' => 0,
        'compress' => 0,
        // перечень глобальных переменных, которые будут доступны в XML документе на всех страницах
        'vars' => array(
            'SOME_GLOBAL_XML_VARIABLE' => 'some constant value',
            'ANOTHER_GLOBAL_XML_VARIABLE' => 'another value',
        ),
    ),

    // насткойки кеша memcached
    'cache' => array(
        'enable' => 0,
        'host' => '127.0.0.1',
        'port' => '11211'
    ),

    // настройки документа
    'document' => array(
        // основная точка входа в xslt преобразователь
        'transformer' => 'main.xslt',
        // насткойка кеширования xslt
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
    ),

    // дополнительные внешние системы авторизации
    'auth' => array(
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
    ),

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
        'public' => '',
        'private' => ''
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
);

