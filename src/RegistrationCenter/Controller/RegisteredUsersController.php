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
use RegistrationCenter\Resources\Views\RegisteredUsersView;


/**
 * Class RegisteredUsersController
 * @package RegistrationCenter\Controller
 */
class RegisteredUsersController extends Controller
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
     * @var RegisteredUsersView
     */
    private $view;


    /**
     * RegisteredUsersController constructor.
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->home = new HomeController();
        $this->view = new RegisteredUsersView();
    }

    /**
     *
     */
    public function index()
    {
        $this->home->head();


        $stmt = $this->db->prepare("SELECT rc_user_tbl.user_id AS user_id,
                                    server_tbl.server_domain AS server_domain,
                                    rc_user_tbl.server_id AS server_id,
                                    rc_user_tbl.UXi,
                                    rc_user_tbl.EXi,
                                    rc_user_tbl.Rcov
                                    FROM rc_user_tbl
                                    INNER JOIN server_tbl ON (rc_user_tbl.server_id = server_tbl.server_id)
                                    ORDER BY rc_user_tbl.id DESC");
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