<?php namespace App\Libraries;

use Config\App;
use Config\Services;

class Auth
{
    /**
     * @var \CodeIgniter\Session\Session
     */
    protected $session;
    /**
     * @var \CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;

    public function __construct()
    {
        $this->session = Services::session(new App());
        $this->request = Services::request();

        helper('cookie');
    }

    public function isConnected()
    {
        if (uri_string() != 'admin/auth/login' && uri_string() != 'admin/auth/login_ajax') {
            $cookie = get_cookie('remember_me', true);

            if ($cookie) {
                $this->session->set('Account_ip', $this->request->getIPAddress());
                $this->session->set('Account_id', $cookie);
                $this->session->set('logged_in', true);
                set_cookie(['name' => 'remember_me', 'value' => $cookie, 'expire' => '32140800'], true);
            }

            if (!$this->session->has('logged_in')) {
                if ($cookie) {
                    return redirect($_SERVER['REQUEST_URI']);
                }

                return false;
            }

            return true;
        }

        return true;
    }
}
