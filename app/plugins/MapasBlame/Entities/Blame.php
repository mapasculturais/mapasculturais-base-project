<?php
namespace MapasBlame\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property User $user
 * 
 * @ORM\Table(name="blame")
 * @ORM\Entity(readOnly=true)
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * 
 */
class Blame extends \MapasCulturais\Entity 
{   

    /**
     * @var integer
     *
     * @ORM\Column(name="log_id", type="integer")
     * @ORM\Id
     */
    protected $id;


    /**
     * @var string
     * 
     * @ORM\Column(name="request_id", type="string")
     */
    protected $requestId;


    /**
     * @var string
     * 
     * @ORM\Column(name="session_id", type="string")
     */
    protected $sessionId;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var int
     * 
     * @ORM\Column(name="user_id", type="string")
     */
    protected $userId;

    /**
     * @var string
     * 
     * @ORM\Column(name="ip", type="string")
     */
    protected $ip;


    /**
     * @var string
     * 
     * @ORM\Column(name="action", type="string")
     */
    protected $action;


    /**
     * @var string
     * 
     * @ORM\Column(name="user_agent", type="string")
     */
    protected $userAgent;
     
    
    /**
     * @var string
     * 
     * @ORM\Column(name="user_browser_name", type="string")
     */
    protected $userBrowserName;
    

    /**
     * @var string
     * 
     * @ORM\Column(name="user_browser_version", type="string")
     */
    protected $userBrowserVersion;
    

    /**
     * @var string
     * 
     * @ORM\Column(name="user_os", type="string")
     */
    protected $userOS;


    /**
     * @var string
     * 
     * @ORM\Column(name="user_device", type="string")
     */
    protected $userDevice;


    /**
     * @var object
     *
     * @ORM\Column(name="request_metadata", type="json_array")
     */
    protected $requestMetadata;


    /**
     * @var object
     *
     * @ORM\Column(name="log_metadata", type="json_array")
     */
    protected $logMetadata;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="request_ts", type="datetime")
     */
    protected $requestTimestamp;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="log_ts", type="datetime")
     */
    protected $logTimestamp;


    private function __construct() 
    { 
    }

}