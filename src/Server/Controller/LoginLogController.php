<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/4/2017
 * Time: 12:43 PM
 */

namespace Server\Controller;


use Core\Controller;
use Home\Controller\HomeController;
use Server\Resources\Views\LoginLogView;

class LoginLogController extends Controller
{
    /**
     * @var \PDO
     */
    private $db;
    /**
     * @var HomeController
     */
    private $home;

    private $view;

    /**
     * DataCenterController constructor.
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->home = new HomeController();
        $this->view = new LoginLogView();
    }

    /**
     *
     */
    public function index()
    {
        $this->home->head();


        $stmt = $this->db->prepare("SELECT
                                  `login_log_tbl`.`id`,
                                  `login_log_tbl`.`user_id`,
                                  `login_log_tbl`.`server_id`,
                                  `server_tbl`.`server_domain`,
                                  DATE_FORMAT(`login_log_tbl`.`date_time`, '%d-%m-%Y %H:%i:%s') AS `date_time`
                                FROM `login_log_tbl`
                                INNER JOIN `server_tbl` ON (`login_log_tbl`.`server_id` = `server_tbl`.`server_id`)
                                ORDER BY `login_log_tbl`.`id` DESC");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->body($result);
        }

        $this->body_bottom();
        $this->home->foot();
    }

    /**
     * @param array $result
     */
    public function body($result = array())
    {
        $this->view->body($result);
    }

    /**
     *
     */
    public function body_bottom()
    {
        $this->view->body_bottom();
    }
}