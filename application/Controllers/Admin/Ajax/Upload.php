<?php namespace App\Controllers\Admin\Ajax;

use App\Models\Admin\MediaModel;
use Bulletproof\Image;

class Upload extends Ajax
{
    protected $image;
    public function __construct(...$params)
    {
        parent::__construct(...$params);
        $this->image = new Image($_FILES);
        $this->media_model = new MediaModel();
    }

    public function index()
    {
        if ($this->isConnected()) {
            if ($this->csrf->validateToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
                $this->image->setLocation(FCPATH . 'uploads/' . date('Y') . '/' . date('n'));
                if ($this->image["pictures"]) {
                    $name = substr($_FILES['pictures']['name'], 0, strrpos($_FILES['pictures']['name'], "."));
                    $this->image->setName($name);
                    $upload = $this->image->upload();
                    if ($upload) {
                        $id_pic = $this->media_model->Add('uploads/' . date('Y') . '/' . date('n') . '/', $this->image->getName() . "." . $this->image->getMime());
                        return $this->responded(["code" => 1, "title" => "Uploads", "message" => 'Image sauvegarder', "id" => $id_pic, "slug" => 'uploads/' . date('Y') . '/' . date('n') . '/' . $this->image->getName() . "." . $this->image->getMime()]);
                    } else {
                        return $this->responded(["code" => 0, "message" => "Erreur : " . $this->image["error"]]);
                    }
                } else {
                    return $this->responded(["code" => 0, "message" => "Erreur : " . $this->image["error"]]);
                }
            } else {
                return $this->responded(["code" => 0]);
            }
        } else {
            return $this->responded(["code" => 0]);
        }
    }
}