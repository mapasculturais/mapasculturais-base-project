<?php
namespace MapasBlame;

use DateTime;
use MapasCulturais\App;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Device;
use Sinergi\BrowserDetector\Os;

class Request {

    protected $conn;
    protected $isNew = true;

    public $id;
    public $browser;
    public $os;
    public $device;

    public $ip;
    public $sessionId;
    public $userId;
    public $userAgent;

    public $metadata;

    function __construct(array $metadata = [])
    { 
        $app = App::i();
        
        $this->conn = $app->em->getConnection();
        
        $this->id = $app->getToken(13);
        $this->metadata = (object) $metadata;
        
        $this->ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->sessionId = session_id();
        $this->userId = $app->user->id;
        
        $this->browser = new Browser;
        $this->os = new Os;
        $this->device = new Device;
    }

    function save() {
        $data = [
            'id' => $this->id,
            'ip' => $this->ip,
            'session_id' => $this->sessionId,
            'user_id' => $this->userId,
            'metadata' => json_encode($this->metadata),
            'user_agent' => $this->userAgent,
            'user_browser_name' => $this->browser->getName(),
            'user_browser_version' => $this->browser->getVersion(),
            'user_os' => $this->os->getName(),
            'user_device' => $this->device->getName(),
            'created_at' => (new DateTime())->format("Y-m-d H:i:s")
        ];

        $this->conn->insert('blame_request', $data);

        $this->isNew = false;
    }

    function log($action, $metadata)
    {
        if ($this->isNew) {
            $this->save();
        }

        $data = [
            'request_id' => $this->id,
            'action' => $action,
            'metadata' => json_encode($metadata),
            'created_at' => (new DateTime())->format("Y-m-d H:i:s")
        ];

        $this->conn->insert('blame_log', $data);
    }
}