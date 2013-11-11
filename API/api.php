<?php

class API
{
    
    protected $db;
    protected $user;
    protected $header;


    public function __construct() {
        
        global $db, $ir, $h;
        
        $this->db = $db;
        $this->user = $ir;
        $this->header = $h;
    }
    
    /**
     * Adds an event to the given user
     * @param string|int $userid Accepts username or UserID
     * @param type $text
     * @return boolean
     */
    public function addEvent($userid, $text)
    {
        
        //If the userid is a string then convert it to a int
        if((string) $userid)
        {
            $userid = $this->name2id($userid);
        }
        
        $this->db->query("INSERT INTO events (`evUSER`, `evTIME`, `evTEXT`) VALUES ({$userid}, {time()}, {$this->db->escape($text)}");
        $this->db->query("UPDATE users SET new_events = new_events+1 WHERE userid = {$userid}");
        
        return true;
    }
    
    /**
     * Checks against the current userlevel for the required, if greater than or equal to then allow access
     * @param int $requiredLevel
     * @return boolean
     */
    public function canView($requiredLevel)
    {
        if(in_array($this->user['user_level'], $requiredLevel))
        {
            return true;
        }
    }
    
    /**
     * Checks if the user is logged in, returns true if they are
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
    public function accessDenied()
    {
        $output = 'You do not have permission to view this page';
        
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
     * Escapes output
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string);
    }
    
    /**
     * Converts a userid into a username
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
     * Converts a username into an ID
     * @param string $name
     * @return int
     */
    public function name2id($name)
    {
        $query = $this->db->query("SELECT id FROM users WHERE username = {{$this->db->escape($name)} LIMIT 1");
        
        if($query->num_rows == 0)
        {
            return 'Non existant';
        }
        
        $fetch = $query->fetch_object();
        
        return $this->escape($fetch->id);
    }
    
    /**
     * Outputs a pretty date
     * @param DateTime $date
     * @return string
     */
    public function niceDate($date)
    {
        $date = new DateTime($date);
        
        return $date->format('d/m/Y  \- g:i A');
    }
    
    /**
     * Checks if the user is in jail, return true if they are
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
     * Checks if the user is in hospital, returns true if they are
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
     * Checks if the user has any donator days remaining, returns true if they do
     * @return boolean
     */
    public function isDonator()
    {
        if($this->user['donator_days'] > 0)
        {
            return true;
        }
    }
}