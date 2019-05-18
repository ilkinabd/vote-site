<?php


namespace Classes;


use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class BackendUserProvider implements UserProviderInterface
{

    private $conn;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param $login
     * @return BackendUser
     */
    public function loadUserByUsername($login)
    {

        $qb = $this->conn->createQueryBuilder();

        $res = $qb->select('u.user_login', 'u.user_pass', 'u.user_name', 'u.user_surname','u.user_roles')
            ->from('backend_user', 'u')
            ->where('u.user_login = ?')
            ->setParameter(0, $login)
            ->execute();

        if (!$user = $res->fetch()) {
            throw new UsernameNotFoundException(sprintf('User with login "%s" does not exist.', $login));
        }

        return new BackendUser($user['user_login'], $user['user_pass'], $user['user_name'],
            $user['user_surname'], explode(',', $user['user_roles']));
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     * @return BackendUser
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof BackendUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === BackendUser::class;
    }
}