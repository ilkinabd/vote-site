<?php
/**
 * Created by PhpStorm.
 * User: xterminate
 * Date: 11.03.2019
 * Time: 23:37
 */

namespace Classes;


use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;

class User
{
    private $id;
    private $external_id;
    private $first_name;
    private $last_name;
    private $voted;
    private $singer_id;
    private $auth_type;
    private $avatar;
    private $last_login;
    private $last_logout;
    private $last_voted;

    /**
     * @return DateTime
     * @throws Exception
     */
    public function getLastLogin()
    {
        return new DateTime($this->last_login);
    }

    /**
     * @param mixed $last_login
     */
    public function setLastLogin($last_login)
    {
        $this->last_login = $last_login;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    public function getLastLogout()
    {
        return new DateTime($this->last_logout);
    }

    /**
     * @param mixed $last_logout
     */
    public function setLastLogout($last_logout)
    {
        $this->last_logout = $last_logout;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    public function getLastVoted()
    {
        return new DateTime($this->last_voted);
    }

    /**
     * @param mixed $last_voted
     */
    public function setLastVoted($last_voted)
    {
        $this->last_voted = $last_voted;
    }


    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @param mixed $external_id
     */
    public function setExternalId($external_id)
    {
        $this->external_id = $external_id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return mixed
     */
    public function getVoted()
    {
        return $this->voted;
    }

    /**
     * @param mixed $voted
     */
    public function setVoted($voted)
    {
        $this->voted = $voted;
    }

    /**
     * @return mixed
     */
    public function getSingerId()
    {
        return $this->singer_id;
    }

    /**
     * @param mixed $singer_id
     */
    public function setSingerId($singer_id)
    {
        $this->singer_id = $singer_id;
    }

    /**
     * @return mixed
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * @param mixed $auth_type
     */
    public function setAuthType($auth_type)
    {
        $this->auth_type = $auth_type;
    }

    public function canVote()
    {
        return $this->voted == 0;
    }

    /**
     * @param Connection $db
     * @param $singer_id
     * @return User
     * @throws DBALException
     */
    public function vote($db, $singer_id)
    {
        $preVote = $db->fetchColumn('select vote_count from vote where poll_option_id = ?;',
            array($singer_id), 0);
        $preVote = intval($preVote);

        if ($preVote > 0) {
            $db->executeQuery('update vote set vote_count = vote_count + 1 where poll_option_id = ?',
                array($singer_id));
        } else {

            $db->insert('vote', array(
                'poll_id' => 1,
                'poll_option_id' => $singer_id,
                'vote_count' => 1
            ));
        }

        $db->update('ordinary_user', array(
            'voted' => '1',
            'singer_id' => $singer_id,
            'last_voted' => (new DateTime('NOW'))->format('Y-m-d H:i:s')
        ), array('external_id' => $this->external_id));

        $this->setVoted(1);
        $this->setSingerId($singer_id);

        // $date = (new DateTime('NOW'))->format('Y-m-d H:i:s');
        // $db->insert('vote_log', array(
        //     'user_external_id' => $this->external_id,
        //     'user_auth_type' => $this->auth_type,
        //     'singer_id' => $singer_id,
        //     'date' => $date
        // ));

        return $this;
    }
}