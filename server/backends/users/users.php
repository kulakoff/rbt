<?php

    /**
     * backends users namespace
     */

    namespace backends\users {

        use backends\backend;

        /**
         * base users class
         */

        abstract class users extends backend {

            /**
             * @param $config
             * @param $db
             * @param $redis
             */

            function __construct($config, $db, $redis, $login = false)
            {
                parent::__construct($config, $db, $redis, $login);

                require_once __DIR__ . '/../../utils/clickhouse.php';

                $this->clickhouse = new \clickhouse(
                    @$config['clickhouse']['host'] ?: '127.0.0.1',
                    @$config['clickhouse']['port'] ?: 8123,
                    @$config['clickhouse']['username'] ?: 'default',
                    @$config['clickhouse']['password'] ?: 'qqq',
                    @$config['clickhouse']['database'] ?: 'default'
                );
            }

            /**
             * get list of all users
             *
             * @param boolean $withSessions
             *
             * @return array
             */

            abstract public function getUsers($withSessions = false);

            /**
             * get user by uid
             *
             * @param integer $uid uid
             *
             * @return array
             */

            abstract public function getUser($uid);

            /**
             * get uid by e-mail
             *
             * @param string $eMail e-mail
             *
             * @return false|integer
             */

            abstract public function getUidByEMail($eMail);

            /**
             * @param $login
             * @return mixed
             */

            abstract public function getUidByLogin($login);

            /**
             * add user
             *
             * @param string $login
             * @param string $realName
             * @param string $eMail
             * @param string $phone
             *
             * @return integer
             */

            abstract public function addUser($login, $realName = null, $eMail = null, $phone = null);

            /**
             * set password
             *
             * @param integer $uid
             * @param string $password
             *
             * @return mixed
             */

            abstract public function setPassword($uid, $password);

            /**
             * delete user
             *
             * @param $uid
             *
             * @return boolean
             */

            abstract public function deleteUser($uid);

            /**
             * modify user data
             *
             * @param integer $uid
             * @param string $realName
             * @param string $eMail
             * @param string $phone
             * @param string $tg
             * @param string $notification
             * @param boolean $enabled
             * @param string $defaultRoute
             * @param mixed $persistentToken
             * @param integer $primaryGroup
             *
             * @return boolean
             */

            abstract public function modifyUser($uid, $realName = '', $eMail = '', $phone = '', $tg = '', $notification = 'tgEmail', $enabled = true, $defaultRoute = '', $persistentToken = false, $primaryGroup = -1);

            /**
             * @param string $tg
             * @param string $subject
             * @param string $message
             * @param string $token
             *
             * @return mixed
             */

            private function sendTg($tg, $subject, $message, $token)
            {
                if ($tg && $token) {
                    try {
                        $tg = @json_decode(file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?chat_id=" . urlencode($tg) . "&text=" . urlencode($subject . "\n\n" . $message)), true);
                        return $tg && @$tg["ok"];
                    } catch (\Exception $e) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            /**
             * @param string $login
             * @param string $email
             * @param string $subject
             * @param string $message
             * @param string $config
             *
             * @return mixed
             */

            private function sendEmail($login, $email, $subject, $message, $config) {
                try {
                    if ($email && $config && $login != $email) {
                        return eMail($config["email"], $email, $subject ? : "-", $message) === true;
                    } else {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            /**
             * @param string $uid
             * @param string $subject
             * @param string $message
             *
             * @return mixed
             */

            public function notify($uid, $subject, $message) {
                if (!checkInt($uid)) {
                    return false;
                }

                $user = $this->getUser($uid);

                if (!$user) {
                    return false;
                }

                if (!in_array($user["notification"], [ "tgEmail", "emailTg", "tg", "email" ])) {
                    return false;
                }

                if ($user["notification"] == "tg" && (!$user["tg"] || !@$this->config["telegram"]["bot"])) {
                    return false;
                }

                if ($user["notification"] == "email" && (!$user["eMail"] || !$this->config["email"])) {
                    return false;
                }

                $message = trim($message);
                $subject = trim($subject);

                if (!$message) {
                    return false;
                }

                $this->clickhouse->insert("nlog", [ [ "date" => time(), "login" => $this->login, "subject" => $subject, "message" => $message, "target" => $user["notification"] ] ]);

                if ($user["notification"] == "tg") {
                    return $this->sendTg($user["tg"], $subject, $message, @$this->config["telegram"]["bot"]);
                }

                if ($user["notification"] == "tgEmail") {
                    return $this->sendTg(@$user["tg"], $subject, $message, @$this->config["telegram"]["bot"]) || $this->sendEmail($login, @$user["eMail"], $subject, $message, $this->config);
                }

                if ($user["notification"] == "email") {
                    return $this->sendEmail($login, $user["eMail"], $subject, $message, $this->config);
                }

                if ($user["notification"] == "emailTg") {
                    return $this->sendEmail($login, @$user["eMail"], $subject, $message, $this->config) || $this->sendTg(@$user["tg"], $subject, $message, @$this->config["telegram"]["bot"]);
                }
            }

            /**
             * @return mixed
             */

            abstract public function getSettings();

            /**
             * @param string $settings
             */

            abstract public function putSettings($settings);

            /**
             * @param string $from
             * @param string $to
             * @param string $subject
             * @param string $body
             * @param string $type
             * @param string $handler
             *
             * @return mixed
             */

            abstract public function sendMessage($from, $to, $subject, $body, $type, $handler);

            /**
             * @param string $login
             *
             * @return mixed
             */

            abstract public function unreaded($uid);

            /**
             * @param string $id
             *
             * @return mixed
             */

            abstract public function readed($id);

            /**
             * @param array $ids
             *
             * @return mixed
             */

            abstract public function getMessages($ids);

            /**
             * @param array $ids
             *
             * @return mixed
             */

            abstract public function deleteMessages($ids);

            /**
             * @param integer $uid
             * @param string $secret
             *
             * @return mixed
             */

            abstract public function two_fa($uid, $secret = "");
        }
    }