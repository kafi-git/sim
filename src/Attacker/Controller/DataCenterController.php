<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/4/2017
 * Time: 11:59 AM
 */

namespace Attacker\Controller;


use Attacker\Resources\Views\DataCenterView;
use Core\Controller;
use Home\Controller\HomeController;

/**
 * Class DataCenterController
 * @package Attacker\Controller
 */
class DataCenterController extends Controller
{
    /**
     * @var \PDO
     */
    private $db;
    /**
     * @var HomeController
     */
    private $home;

    /**
     * @var DataCenterView
     */
    private $view;

    /**
     * DataCenterController constructor.
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->home = new HomeController();
        $this->view = new DataCenterView();
    }

    /**
     *
     */
    public function index()
    {
        $this->home->head();


        $stmt = $this->db->prepare("SELECT
                                  `attacker_tbl`.`id`,
                                  `attacker_tbl`.`user_id`,
                                  `server_tbl`.`server_domain`,
                                  `attacker_tbl`.`server_id`,
                                  `attacker_tbl`.`M1`,
                                  `attacker_tbl`.`M2`,
                                  `attacker_tbl`.`M4`,
                                  `attacker_tbl`.`M5`,
                                  `attacker_tbl`.`M7`,
                                  DATE_FORMAT(`attacker_tbl`.`date_time`, '%d-%m-%Y %H:%i:%s') AS date_time
                                FROM `attacker_tbl`
                                INNER JOIN `server_tbl` ON (`attacker_tbl`.`server_id` = `server_tbl`.`server_id`)
                                ORDER BY `attacker_tbl`.`id` DESC");
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