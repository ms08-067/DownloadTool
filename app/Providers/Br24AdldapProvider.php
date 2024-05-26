<?php

namespace App\Providers;

use Adldap\Connections\Provider as AdldapProvider;
use Adldap\Auth\BindException;
use Throwable;
use Exception;

class Br24AdldapProvider extends AdldapProvider
{

    /**
     * {@inheritdoc}
     */
    public function connectWhenOtherSituation($username = null, $password = null)
    {
        // Get the default guard instance.
        $guard = $this->getGuard();

        if (is_null($username) && is_null($password)) {
            // If both the username and password are null, we'll connect to the server
            // using the configured administrator username and password.
            $result_custombindAsAdministratorOther = $this->custombindAsAdministratorOther($guard);
        } else {
            // Bind to the server with the specified username and password otherwise.
            $guard->bind($username, $password);
        }

        // dump($result_custombindAsAdministratorOther);
        // dump("connectWhenOtherSituation()");

        if($result_custombindAsAdministratorOther === "Can't contact LDAP server"){
            return $result_custombindAsAdministratorOther;
        }
        return $this;
    }


    public function custombindAsAdministratorOther($guard)
    {
        try{
            $result_custombind = $this->custombind(
                $this->configuration->get('username'),
                $this->configuration->get('password')
            );
            // dump($result_custombind);
            // dump("custombindAsAdministratorOther return result_custombind");

            if($result_custombind === "Can't contact LDAP server"){
                return $result_custombind;
            }
            //dump("afterwards do next ");
            $result = true;
        } catch(BindException $e){
            $result = false;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function custombind($username = null, $password = null)
    {
        $this->fireBindingEvent($username, $password);

        // dump($this->connection);
        // // dump($username);
        // // dump($password);
        // dump(@$this->connection->bind($username, $password));

        try {
            if (@$this->connection->bind($username, $password) === true) {
                $this->fireBoundEvent($username, $password);
            } else {
                throw new Exception($this->connection->getLastError(), $this->connection->errNo());
            }
        } catch (Throwable $e) {
            $this->fireFailedEvent($username, $password);

            if($e->getMessage() == "Can't contact LDAP server"){
                return $e->getMessage();
            }
            //throw (new BindException($e->getMessage(), $e->getCode(), $e))->setDetailedError($this->connection->getDetailedError());
        }
    }


    /**
     * Fire the attempting event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireAttemptingEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Attempting($this->connection, $username, $password));
        }
    }

    /**
     * Fire the passed event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function firePassedEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Passed($this->connection, $username, $password));
        }
    }

    /**
     * Fire the failed event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireFailedEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Failed($this->connection, $username, $password));
        }
    }

    /**
     * Fire the binding event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireBindingEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Binding($this->connection, $username, $password));
        }
    }

    /**
     * Fire the bound event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireBoundEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Bound($this->connection, $username, $password));
        }
    }

}
