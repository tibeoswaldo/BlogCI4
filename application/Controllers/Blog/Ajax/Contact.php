<?php namespace App\Controllers\Blog\Ajax;

use App\Models\Blog\ContactModel;

/**
 * Class Comments
 *
 * @package App\Controllers\Blog\Ajax
 */
class Contact extends Ajax
{

    /**
     * @var \App\Models\Blog\CommentsModel
     */
    private $contact_model;

    /**
     * Contact constructor.
     *
     * @param array ...$params
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);
        $this->contact_model = new ContactModel();
    }

    /**
     * @return bool
     */
    public function addContact()
    {
        if ($this->csrf->validateToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
            if ($this->request->isValidIP($this->request->getIPAddress())) {
                $this->contact_model->Add($_POST['name'], $_POST['email'], $_POST['sujet'], $_POST['message'], $this->request->getIPAddress());
                $this->Render(["message" => "La prise de contact à bien été envoyez, vous receverez une réponse d'ici 24h à 48h", "code" => 1]);
                return true;
            } else {
                $this->Render(["message" => "Error : yout IP is bizzar ?"], true);
                return false;
            }
        } else {
            $this->Render(["message" => "Error CSRF, You are HACKER ?"], true);
            return false;
        }
    }
}