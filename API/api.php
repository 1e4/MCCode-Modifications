<?php
class API
{
    
    protected $db;
    public $user;
    protected $header;
    public $set;
    
    protected $moduleSettings = array();
    protected $module;


    public function __construct() {
        
        global $db, $ir, $h, $set;
        
        $this->db = $db;
        
        $this->user = $ir;
        $this->header = $h;
        $this->set = $set;
                
//        var_dump($this->user);
    }
    
    /**
     * Gives the user an event
     * @param int $userid
     * @param string $text
     */
    public function addEvent($userid, $text)
    {
        $this->db->query("INSERT INTO events (`evUSER`, `evTIME`, `evTEXT`) VALUES ({$userid}, {time()}, {$text}");
        $this->db->query("UPDATE users SET new_events = new_events+1 WHERE userid = {$userid}");
    }
    
    /**
     * Checks against multiple options
     * Checks if a donator
     * Checks if in Jail
     * Checks if in hospital
     * Checks if has the required userlevel
     * 
     * @param array $config
     * @return boolean
     */
    public function canView($config)
    {
        
        if(array_key_exists('accessInJail', $config))
        {
            //If the user is in jail and the page cannot be accessed in jail deny access
            if($config['accessInJail'] == 0 && $this->inJail())
            {
                return 'jail';
            }
        }
        
        if(array_key_exists('accessInHospital', $config))
        {
            //If the user is in hospital and the page cannot be accessed in hospital deny access
            if($config['accessInHospital'] == 0 && $this->inHospital())
            {
                return 'hospital';
            }
        }
        
        if(array_key_exists('donator', $config))
        {
            if($config['donator'] == 1 && !$this->isDonator())
            {
                return 'donator';
            }
        }

        if(array_key_exists('brave_cost', $config))
        {
            if($this->user['brave'] < $config['brave_cost'])
            {
                return 'brave';
            }
        }

        if(array_key_exists('userlevelRequired', $config))
        {
            if(in_array($this->user['user_level'], $config['userlevelRequired']))
            {
                return true;
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Checks if the user is logged in
     * @return boolean
     */
    public function isLoggedIn()
    {
        if(isset($_SESSION['loggedin']))
        {
            if($_SESSION['loggedin'] == 1)
            {
                return true;
            }
        }
    }
    
    /**
     * Returns a basic access denied message
     * @return string
     */
    public function accessDenied($state)
    {
        
        if($state == 'jail')
        {
            $output = 'You are currently in jail, you cannot access this page while in jail';
        }
        elseif($state == 'hospital')
        {
            $output = 'You are currently in hospital, you cannot access this page while in hospital';
        }
        elseif($state == 'donator')
        {
            $output = 'You have to be a donator to access this feature';
        }
        elseif($state == 'brave')
        {
            $output = 'You need more brave for this task';
        }
        else
        {
            $output = 'You do not have permission to view this page';
        }
        
        return $output;
    }
    
    /**
     * Calls the footer and ends the page
     * @return function
     */
    public function endpage()
    {
        return $this->header->endpage();
    }
    
    /**
     * Returns whatever was put in as $string wrapped in entities
     * @todo [15:45:17] Luke: htmlentities($input, ENT_QUOTES, "UTF-8") make it have options
     * @param string$string
     * @return string
     */
    public function escape($string)
    {
        return htmlentities($string);
    }
    
    /**
     * Changes an ID to a name
     * @param int $id
     * @return string
     */
    public function id2name($id)
    {
        $query = $this->db->query("SELECT username FROM users WHERE userid = {$this->db->escape($id)} LIMIT 1");
        
        if($query->num_rows == 0)
        {
            return 'Non existant';
        }
        
        $fetch = $query->fetch_object();
        
        return $this->escape($fetch->username);
    }
    
    /**
     * Retuns a nice date formatted
     * @param DateTime $date
     * @return string
     */
    public function niceDate($date)
    {
        $date = new DateTime($date);
        
        return $date->format('d/m/Y  \- g:i A');
    }
    
    /**
     * Returns true if user is in jail
     * @return boolean
     */
    public function inJail()
    {
        if($this->user['jail'] > 0)
        {
            return true;
        }
    }
    
    /**
     * Returns true if user is in hospital
     * @return boolean
     */
    public function inHospital()
    {
        if($this->user['hospital'] > 0)
        {
            return true;
        }
    }
    
    /**
     * Returns true if a donator
     * @return boolean
     */
    public function isDonator()
    {
        if($this->user['donatordays'] > 0)
        {
            return true;
        }
    }
    
    /**
     * Quick function to put someone in jail
     * @param type $time
     * @param type $reason
     */
    public function putInJail($time, $reason, $userid = -1)
    {
        
        if($userid == -1)
        {
            $userid = $this->user['userid'];
        }
        
        $this->db->query("UPDATE users SET `jail` = {$time}, `jail_reason` = '{$reason}' WHERE userid = '{$userid}'");
    }
    
}
