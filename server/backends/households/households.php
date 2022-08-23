<?php

    /**
    * backends households namespace
    */

    namespace backends\households
    {

        use backends\backend;

        /**
         * base addresses class
         */
        abstract class households extends backend
        {

            /**
             * @param $houseId
             * @return false|array
             */
            abstract function getHouseFlats($houseId);

            /**
             * @param $houseId
             * @return false|array
             */
            abstract function getHouseEntrances($houseId);

            /**
             * @param $houseId
             * @param $entranceType
             * @param $entrance
             * @param $lat
             * @param $lon
             * @param $shared
             * @param $prefix
             * @param $domophoneId
             * @param $domophoneOutput
             * @param $cms
             * @param $cmsType
             * @param $cameraId
             * @param $locksDisabled
             * @param $cmsLevels
             * @return boolean|integer
             */
            abstract function createEntrance($houseId, $entranceType, $entrance, $lat, $lon, $shared, $prefix, $domophoneId, $domophoneOutput, $cms, $cmsType, $cameraId, $locksDisabled, $cmsLevels);

            /**
             * @param $entranceId
             * @return false|array
             */
            abstract function getEntrance($entranceId);

            /**
             * @param $by
             * @param $query
             * @return false|array
             */
            abstract function getEntrances($by, $query);

            /**
             * @param $houseId
             * @param $entranceId
             * @param $prefix
             * @return boolean
             */
            abstract function addEntrance($houseId, $entranceId, $prefix);

            /**
             * @param $entranceId
             * @param $houseId
             * @param $entranceType
             * @param $entrance
             * @param $lat
             * @param $lon
             * @param $shared
             * @param $prefix
             * @param $domophoneId
             * @param $domophoneOutput
             * @param $cms
             * @param $cmsType
             * @param $cameraId
             * @param $locksDisabled
             * @param $cmsLevels
             * @return boolean
             */
            abstract function modifyEntrance($entranceId, $houseId, $entranceType, $entrance, $lat, $lon, $shared, $prefix, $domophoneId, $domophoneOutput, $cms, $cmsType, $cameraId, $locksDisabled, $cmsLevels);

            /**
             * @param $entranceId
             * @param $houseId
             * @return boolean
             */
            abstract function deleteEntrance($entranceId, $houseId);

            /**
             * @param $entranceId
             * @return boolean
             */
            abstract function destroyEntrance($entranceId);

            /**
             * @param $flatId
             * @return boolean|array
             */
            abstract function getFlat($flatId);

            /**
             * @param $by
             * @param $params
             * @return boolean|array
             */
            abstract function getFlats($by, $params);

            /**
             * @param $houseId
             * @param $floor
             * @param $flat
             * @param $entrances
             * @param $apartmentsAndLevels
             * @param $manualBlock
             * @param $openCode
             * @param $autoOpen
             * @param $whiteRabbit
             * @param $sipEnabled
             * @param $sipPassword
             * @return boolean|integer
             */
            abstract function addFlat($houseId, $floor, $flat, $entrances, $apartmentsAndLevels, $manualBlock, $openCode, $autoOpen, $whiteRabbit, $sipEnabled, $sipPassword);

            /**
             * @param $flatId
             * @param $params
             * @return boolean
             */
            abstract function modifyFlat($flatId, $params);

            /**
             * @param $flatId
             * @return boolean
             */
            abstract function deleteFlat($flatId);

            /**
             * @param $flatId
             * @return boolean
             */
            abstract function doorOpened($flatId);

            /**
             * @param $houseId
             * @return false|array
             */
            abstract function getSharedEntrances($houseId = false);

            /**
             * @param $entranceId
             * @return false|array
             */
            abstract public function getCms($entranceId);

            /**
             * @param $entranceId
             * @param $cms
             * @return boolean
             */
            abstract public function setCms($entranceId, $cms);

            /**
             * @return false|array
             */
            abstract public function getDomophones();

            /**
             * @return mixed
             */
            public function getAsteriskServers() {
                return $this->config["asterisk_servers"];
            }

            /**
             * @return false|array
             */
            abstract public function getModels();

            /**
             * @return false|array
             */
            abstract public function getCMSes();

            /**
             * @param $enabled
             * @param $model
             * @param $server
             * @param $url
             * @param $credentials
             * @param $callerId
             * @param $dtmf
             * @param $comment
             * @return false|integer
             */
            abstract public function addDomophone($enabled, $model, $server, $url, $credentials, $callerId, $dtmf, $comment);

            /**
             * @param $domophoneId
             * @param $enabled
             * @param $model
             * @param $server
             * @param $url
             * @param $credentials
             * @param $callerId
             * @param $dtmf
             * @param $comment
             * @return boolean
             */
            abstract public function modifyDomophone($domophoneId, $enabled, $model, $server, $url, $credentials, $callerId, $dtmf, $comment);

            /**
             * @param $domophoneId
             * @return boolean
             */
            abstract public function deleteDomophone($domophoneId);

            /**
             * @param $domophoneId
             * @return false|array
             */
            abstract public function getDomophone($domophoneId);

            /**
             * @param  integer $subscriberId
             * @param integer $domophoneId
             * @return boolean
             */
            abstract public function isOpeningAllowed($subscriberId, $domophoneId);

            /**
             * @param $by - "id", "mobile", "flat", "...?"
             * @param $query
             * @return false|array
             */
            abstract public function getSubscribers($by, $query);

            /**
             * @param $mobile
             * @param $name
             * @param $patronymic
             * @param $flatId
             * @return boolean|integer
             */
            abstract public function addSubscriber($mobile, $name, $patronymic, $flatId = false);

            /**
             * @param $subscriberId
             * @param $params
             * @return boolean
             */
            abstract public function modifySubscriber($subscriberId, $params = []);

            /**
             * @param $subscriberId
             * @return boolean
             */
            abstract public function deleteSubscriber($subscriberId);

            /**
             * @param $subscriberId
             * @param $flats
             * @return boolean
             */
            abstract public function setSubscriberFlats($subscriberId, $flats);

            /**
             * @param $by
             * @param $query
             * @return mixed
             */
            abstract public function getKeys($by, $query);

            /**
             * @param $rfId
             * @param $accessType
             * @param $accessTo
             * @param $comments
             * @return false|integer
             */
            abstract public function addKey($rfId, $accessType, $accessTo, $comments);

            /**
             * @param $keyId
             * @param $comments
             * @return boolean
             */
            abstract public function modifyKey($keyId, $comments);

            /**
             * @param $keyId
             * @return boolean
             */
            abstract public function deleteKey($keyId);

            /**
             * @param $token
             * @return boolean
             */
            abstract public function dismissToken($token);
        }
    }
