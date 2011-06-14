<?php
return array(
    'project' => 'Platform 2.6',
    'database' => array(
        'master' => array(
            'dsn' => 'host=localhost;port=3306;dbname=energine-2.6',
            'username' => '',
            'password' => ''
        )
    ),
    'site' => array(
        'domain' => 'energine-2.6.dev',
        'debug' => 1,
        'asXML' => 0,
        'compress' => 1,
        'root' => '/'
    ),
    'cache' => array(
        'enable' => 0,
        'host' => '127.0.0.1',
        'port' => '11211'
    ),
    'document' => array(
        'transformer' => 'main.xslt',
        'xslcache' => 0,
        'xslprofile' => 0
    ),
    'session' => array(
        'timeout' => 6000,
        'lifespan' => 108000,
    ),
    'mail' => array(
        'from' => 'noreply@energine.org',
        'manager' => 'demo@energine.org',
        'feedback' => 'demo@energine.org'
    ),
    'video' => array(
        'ffmpeg' => '/usr/bin/ffmpeg'
    ),
    'google' => array(
        'verify' => '',
        'analytics' => ''
    )
);

