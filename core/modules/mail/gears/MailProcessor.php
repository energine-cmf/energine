<?php

namespace Energine\mail\gears;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Energine\share\gears\DBWorker;
use Energine\mail\gears\MailSourceFactory;
use Energine\mail\gears\Mail;
use Energine\share\gears\QAL;

class MailProcessor
{
    use DBWorker;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct() {

    }

    public function registerInputInterface(InputInterface $input) {
        $this->input = $input;
        return $this;
    }

    public function registerOutputInterface(OutputInterface $output) {
        $this->output = $output;
        return $this;
    }

    protected function log($message, $hilight = false, $finalize = false) {
        $this->output->write(date('Y-m-d h:i:s') . ': ');
        if ($hilight) {
            $this->output->writeln('<comment>' . $message . '</comment>');
        } else {
            $this->output->writeln('<info>' . $message . '</info>');
        }
        if ($finalize) $this->output->writeln('');
    }

    protected function getDefaultLanguage() {
        return E()->getLanguage()->getDefault();
    }

    protected function getActiveSubscriptions() {
        return $this->dbh->select(
            'select
                s.subscription_id as id,
                s.subscription_is_active as is_active,
                s.subscription_type as `type`,
                st.subscription_name as `name`,
                s.subscription_sent_date as sent_date,
                s.subscription_period as `period`
            from mail_subscriptions s
            left join mail_subscriptions_translation st
                on s.subscription_id = st.subscription_id and st.lang_id = %s
            where s.subscription_is_active = 1',
            $this->getDefaultLanguage()
        );
    }

    public function getSubscribers($subscription_id) {
        $res = $this->dbh->select(
            'select DISTINCT u.u_name, u.u_fullname
            from mail_subscriptions2users su
            left join user_users u on su.u_id = u.u_id
            where su.subscription_id = %s
            UNION
            select DISTINCT me_name as u_name, %s as u_fullname
            from mail_email2subscriptions esu
            left join mail_email_subscribers es on esu.me_id = es.me_id
            where esu.subscription_id = %1$s
            ', $subscription_id, $this->translate('TXT_EMAIL_USER')
        );
        $result = [];
        if ($res) {
            foreach($res as $row) {
                $result[$row['u_name']] = $row['u_fullname'];
            }
        }
        return $result;
    }

    protected function processSubscription($subscription) {
        $this->log(sprintf('Processing subscription id=%s "%s"', $subscription['id'], $subscription['name']));

        $now = new \DateTime();
        $last_sent = new \DateTime((empty($subscription['sent_date'])) ? '2000-01-01 00:00:00' : $subscription['sent_date'] );
        $desired = clone $last_sent;
        switch ($subscription['period']) {
            case 'hourly':
                $desired->add(new \DateInterval('PT1H'));
                break;
            case 'daily':
                $desired->add(new \DateInterval('PT24H'));
                break;
            case 'weekly':
                $desired->add(new \DateInterval('PT168H'));
                break;
            case 'monthly':
                $desired->add(new \DateInterval('PT5040H'));
                break;
        }

        if ($desired > $now) {
            $this->log(sprintf('Processing is not required due to last sent date %s and %s period', $last_sent->format('Y-m-d H:i:s'), $subscription['period']));
        } else {
            $this->log(sprintf('Processing is required due to last sent date %s and %s period', $last_sent->format('Y-m-d H:i:s'), $subscription['period']));

            $subscribers = $this->getSubscribers($subscription['id']);

            if (empty($subscribers)) {
                $this->log('No subscribers found');
            } else {
                $this->log(sprintf('Found %s subscribers', count($subscribers)));

                try {
                    $source = MailSourceFactory::getByName($subscription['type']);
                    $source->setLang($this->getDefaultLanguage());
                    $since_date = new \DateTime($subscription['sent_date'] ? $subscription['sent_date'] : '2000-00-00 00:00:00');
                    $items = $source->getItemsSinceDate($since_date);
                    $this->log(sprintf('Found %s items in subscription', count($items)));

                    foreach ($subscribers as $email => $name) {

                        $this->log(sprintf('Sending %s mail to %s (%s)', $subscription['type'], $email, $name));

                        $mail = new Mail();
                        $subscriber = ['user_email' => $email, 'user_name' => $name];
                        $mail
                            ->setDebugMode(E()->getConfigValue('site.debug'))
                            ->setSubject($source->getSubject($subscriber))
                            ->setText($source->getBody($subscriber, $items))
                            ->setHtmlText($source->getHTMLBody($subscriber, $items))
                            ->setFrom(E()->getConfigValue('mail.from'))
                            ->addTo($email, $name)
                            ->send();
                    }

                    $last_sent = new \DateTime();

                    $this->dbh->modify(
                        QAL::UPDATE,
                        'mail_subscriptions',
                        array(
                            'subscription_sent_date' => $last_sent->format('Y-m-d H:i:s'),
                        ),
                        array(
                            'subscription_id' => $subscription['id']
                        )
                    );

                } catch (\Exception $e) {
                    $this -> log('Error processing subscription: ' . (string) $e);
                }

            }

        }
    }

    public function run() {
        $this->log('Getting active subscriptions');
        $subscriptions = $this->getActiveSubscriptions();
        $this->log(sprintf('Found %s active subscriptions', count($subscriptions)));
        if ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                $this->processSubscription($subscription);
            }
        }
        $this->log('Done', true, true);
    }

}
