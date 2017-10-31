<?php namespace App\Libraries;

use App\Models\Admin\ConfigModel;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class Mailer
{
    protected $mailp;
    protected $config_model;

    public function __construct()
    {
        $this->config_model = new ConfigModel();
        $transport = (new Swift_SmtpTransport($this->config_model->GetConfig('mail_host'), $this->config_model->GetConfig('mail_port'), $this->config_model->GetConfig('mail_security')))
            ->setUsername($this->config_model->GetConfig('mail_username'))
            ->setPassword($this->config_model->GetConfig('mail_password'));
        $this->mailp = new Swift_Mailer($transport);
    }

    public function sendmail(string $sujet, string $to, string $body)
    {
        $message = (new Swift_Message($sujet))
            ->setFrom([$this->config_model->GetConfig('mail_from_adress') => $this->config_model->GetConfig('mail_from_name')])
            ->setTo([$to])
            ->setBody($body);

        $this->mailp->send($message);
        return true;
    }
}