<?php

namespace dokumentenFreigabe\Model;

use dokumentenFreigabe\DataLayer\Db;
use dokumentenFreigabe\DataLayer\Model;
use dokumentenFreigabe\Model\MysqlSessionHandler;
use dokumentenFreigabe\Model\Session;
use dokumentenFreigabe\DataLayer\AuthMapper;

class Auth 
{

    protected $table_names;
    private $db;
    private $session;
    private $redirect;
    private $sessionHandler;
    private $userId;

    public function __construct($user, $pass)
    {
        $this->tableNames = array('user');
        $this->db = Db::getInstance();
        $this->session = new Session();
        $this->redirect = 'http://localhost/dokumentenFreigabe-backend/';
        $this->sessionHandler = new MysqlSessionHandler();
        $this->user = $user;
        $this->pass = $pass;

    }

    /*
     * Pr�ft Benutzername und Passwort gegen die Datenbank
     * return void
     * @access private
     */
    public function login()
    {

        if ($this->session->getSessionName('password')) {
            $this->validateAuth();

        } else {
            $auth = new AuthMapper($this->user,$this->pass);
            $password = $auth->login();

            if (password_verify($this->pass, $password)) {

                $this->saveSession($password);
            }
        }

    }

    /*
     * Setzt die SessionVariable nach erfolgreichem Login
     * @return void
     * @access protected
     */

    protected function saveSession($password)
    {
        $this->session->setSessionName('login_hash', $password);
        $this->session->setSessionName('password', $password);
        $this->sessionHandler->saveSessionData($password);

    }

    /*
     * Bestaetigt, ob ein bestehender Login noch gueltig ist.
     * return void
     * @access private
     */

    private function validateAuth()
    {
        $hashKey = $this->session->getSessionName('login_hash');
        $passwort = $this->session->getSessionName('password');

        if ($passwort !== $hashKey) {

            $this->logout(true);

        }

    }

    /*
     * Meldet den Benutzer ab
     * @access public
     */

    public function logout($from = false)
    {

        $this->sessionHandler->deleteSession();
        $this->session->deleteSession('password');
        $this->session->deleteSession('login_hash');

        $this->session->destroyCompleteSession();

        //$this->redirect($from);
        return true;
    }

    public function getUserGroup($pass)
    {

        $auth = new AuthMapper($this->user,$this->pass);
        $result = $auth->getUserGroup($pass);
        
        return $result;
    }

    /*
     * Leitet den Browser um und beendet die Ausfuehrung des Scripts
     * @ param boolean die URL, von der dieser Benutzer kam
     */

    private function redirect($from = true)
    {

        if ($from) {
            header('Location:' . $this->redirect . '?from' .
                $_SERVER['REQUEST_URI']);
        } else {
            header('Location:' . $this->redirect);

        }

        exit();
    }

    public function destroySession()
    {
        $_SESSION = array();
        session_destroy();
    }

}
