<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/24/2016
 * Time: 4:34 PM
 */

namespace RegistrationCenter\Controller;

use Core\Controller;
use Home\Controller\HomeController;
use RegistrationCenter\Resources\Views\RegisteredServersView;

/**
 * Class RegisteredServersController
 * @package RegistrationCenter\Controller
 */
class RegisteredServersController extends Controller
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
     * @var RegisteredServersView
     */
    private $view;

    /**
     * RegisteredServersController constructor.
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->home = new HomeController();
        $this->view = new RegisteredServersView();
    }

    /**
     *
     */
    public function index()
    {
        $this->home->head();


        $stmt = $this->db->prepare("SELECT server_tbl.server_domain AS server_domain,
                                    rc_server_tbl.server_id AS server_id,
                                    rc_server_tbl.HKs AS HKs
                                    FROM rc_server_tbl
                                    INNER JOIN server_tbl ON (rc_server_tbl.server_id = server_tbl.server_id)
                                    ORDER BY rc_server_tbl.id DESC");
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