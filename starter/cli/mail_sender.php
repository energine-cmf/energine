#!/usr/bin/env php

<?php

use Energine\mail\gears\MailProcessor;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Application;

@date_default_timezone_set('Europe/Kiev');
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/htdocs/bootstrap.php';

$console = new Application('Mail processor', '0.1');
$console
    ->register('run')
    ->setDescription('Start mail processor')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $output->writeln('Starting mail processor');
        try {
            $processor = new MailProcessor();
            $processor->registerInputInterface($input);
            $processor->registerOutputInterface($output);
            $processor->run();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage().'</error>');
        }
    });
$console->run();