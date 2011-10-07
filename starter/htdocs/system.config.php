<?php
return array(
    'project' => 'Platform 2.6',
    'modules' => array(
        'share',
        'user',
        'apps',
        'forms',
        'seo',
        'calendar'
    ),
    'database' => array(
        'host' => 'localhost',
        'port' => '3306',
        'db' => 'noer_starter',
        'username' => 'noer',
        'password' => 'yohS4uadaose'
    ),
    'site' => array(
        'domain' => 'noer2.eggmen.net',
        'root' => '/starter/',
        'debug' => 1,

        'asXML' => 0,
        'compress' => 0,
    ),
    'cache' => array(
        'enable' => 0,
        'host' => '127.0.0.1',
        'port' => '11211'
    ),
    'document' => array(
        'transformer' => 'main.xslt',
        'xslcache' => 0,
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
    'google' => array(
        'verify' => '',
        'analytics' => ''
    )
);

