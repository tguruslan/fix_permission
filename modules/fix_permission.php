<?php

class FixPrmission
{
    /**
     * Now action
     * @var string
     */
    private $action;

    /**
     * Smarty
     * @var object
     */
    public $smarty;

    /**
     * Smarty template
     * @var object
     */

    public $tpl;

    /**
     * @var 2FAController
     */

    private static $instance;

    private static $users = false;
    private static $usersDir = '/var/brainycp/data/users/';

    public static function getUsers()
    {
        if (self::$users === false) {
            if ($handle = opendir(self::$usersDir)) {
                self::$users = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_dir(self::$usersDir . $file) || in_array($file, array('.', '..'))) {
                        continue;
                    }
                    $config = parse_ini_file(self::$usersDir.$file);
                    self::$users[$config['username']] = $config['username'];
                }
                closedir($handle);
            }
        }

        sort(self::$users);

        return self::$users;
    }

    private static $domains = false;
    private static $vhostsDir = '/var/brainycp/data/vhosts/';

    private function get_domainAction(){
        $user=$_POST['user'];

        if (self::$domains === false) {
            if ($handle = opendir(self::$vhostsDir)) {
                self::$domains = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_dir(self::$vhostsDir . $file) || in_array($file, array('.', '..'))) {
                        continue;
                    }
                    $config = parse_ini_file(self::$vhostsDir.$file);
                    if ($config['user'] == $user){
                        self::$domains[$config['domain']] = $config['domain'];
                    }
                }
                closedir($handle);
            }
        }

        sort(self::$domains);

        $this->smarty->assign('domains', self::$domains);
        $this->tpl->out = $this->smarty->fetch('fix_permission/domains.tpl');

        echo $this->tpl->out;
        die();
    }

    private function fixAction(){
        $site = $_POST['site'];

        $output1=false;
        $output2=false;
        $output3=false;

        if ($handle = opendir(self::$vhostsDir)) {
            while (false !== ($file = readdir($handle))) {
                if (is_dir(self::$vhostsDir . $file) || in_array($file, array('.', '..'))) {
                    continue;
                }
                $config = parse_ini_file(self::$vhostsDir.$file);
                if ($config['domain'] == $site){
                   $output1 = trim(shell_exec("chown -R ".$config['user'].":".$config['user']." ".$config['dir']." && echo 'ok'"));
                   $output2 = trim(shell_exec("find ".$config['dir']." -type d -exec chmod 0755 {} \; && echo 'ok'"));
                   $output3 = trim(shell_exec("find ".$config['dir']." -type f -exec chmod 0644 {} \; && echo 'ok'"));
                }
            }
            closedir($handle);
        }

         if (($output1 == 'ok') && ($output2 == 'ok') && ($output3 == 'ok')){
             echo json_encode(['message'=>'Успіх']);
         }else{
             echo json_encode(['message'=>'Помилка '.$output1." ".$output2." ".$output3]);
         }
        die();

    }

    /**
     * Instantiate and return a factory.
     * @return 2FAController
     */

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Start init page
     * @param object $smarty
     * @param object $tpl
     */

    public function init($smarty, $tpl)
    {
        $this->smarty = $smarty;
        $this->tpl = $tpl;

        $this->setAction();

        if (!method_exists($this, $this->action . 'Action')) {
            $this->action = 'default';
        }

        $methodName = $this->action . 'Action';
        $this->$methodName();
    }

    /**
     * Default index page
     */

    private function defaultAction()
    {
        global $server;
        $this->smarty->assign('users', self::getUsers());
        $this->smarty->assign('g_userinfo', $server->user);


        if(($server->user.group_properties.root != "y") && ($server->user.users_management != "y")){
            if ($handle = opendir(self::$vhostsDir)) {
                self::$domains = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_dir(self::$vhostsDir . $file) || in_array($file, array('.', '..'))) {
                        continue;
                    }
                    $config = parse_ini_file(self::$vhostsDir.$file);
                    if ($config['user'] == $server->user['username']){
                        self::$domains[$config['domain']] = $config['domain'];
                    }
                }
                closedir($handle);
            }
            $this->smarty->assign('domains', self::$domains);
        }


        $this->tpl->out = $this->smarty->fetch('fix_permission/main.tpl');
    }

    /**
     * Auto set action from REQUEST
     */

    public function setAction()
    {
        if (isset($_REQUEST['subdo'])) {
            $this->action = $_REQUEST['subdo'];
        }
    }
}

FixPrmission::getInstance()->init($smarty, $tpl);
