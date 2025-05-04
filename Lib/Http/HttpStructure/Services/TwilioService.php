<?php
namespace App\Lib\Http\HttpStructure\Services;
use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $whatsappNumber;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
        $this->whatsappNumber = env('TWILIO_WHATSAPP_NUMBER');
    }

    public function sendWhatsAppMessage($to, $message)
    {
        $to = "whatsapp:" . $to; // تحويل الرقم ليكون بصيغة واتساب
        return $this->client->messages->create('whatsapp:+201275225555', [
            'from' => 'whatsapp:+14155238886',
            'body' => 'hhhhhhhhhhhh'
        ]);
    }
}
?>
