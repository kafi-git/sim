<?php

/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/3/2017
 * Time: 8:17 PM
 */
namespace Attacker\Controller;

use Core\Controller;

class DataCollectionController extends Controller
{
    private $db;
    private $request;
    private $dateTime;

    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->dateTime = date("Y-m-d H:i:s");
    }

    public function collectLoginRequest()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        $M1 = isset($request["M1"]) ? $request["M1"] : "";
        $M2 = isset($request["M2"]) ? $request["M2"] : "";

        $stmt = $this->db->prepare("INSERT INTO attacker_tbl
                                    (user_id,
                                     server_id,
                                     M1,
                                     M2,
                                     date_time)
                        VALUES (:user_id,
                                :server_id,
                                :M1,
                                :M2,
                                :dateTime)");

        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"], "M1" => $M1, "M2" => $M2, "dateTime" => $this->dateTime));

        $lastInsertID = $this->db->lastInsertId();

        if ($lastInsertID > 0){
            return json_encode(array("flag" => 1, "msg" => "Data Collected", "lid" => $lastInsertID));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Data Collection error"));
        }
    }

    public function collectMua()
    {
        $request = $this->request;

        if (!isset($request["lid"])) {
            return json_encode(array("flag" => 2, "msg" => "No lid found!!"));
        }

        $M4 = isset($request["M4"]) ? $request["M4"] : "";
        $M5 = isset($request["M5"]) ? $request["M5"] : "";

        $stmt = $this->db->prepare("UPDATE attacker_tbl SET M4 = :M4, M5 = :M5, date_time = :dateTime WHERE id = :id");

        $stmt->execute(array("M4" => $M4, "M5" => $M5, "id" => $request["lid"], "dateTime" => $this->dateTime));

        if ($stmt->rowCount() > 0){
            return json_encode(array("flag" => 1, "msg" => "Data Collected"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Data Collection error"));
        }
    }


    public function collectAcknowledgement()
    {
        $request = $this->request;

        if (!isset($request["lid"])) {
            return json_encode(array("flag" => 2, "msg" => "No lid found!!"));
        }

        $M7 = isset($request["M7"]) ? $request["M7"] : "";

        $stmt = $this->db->prepare("UPDATE attacker_tbl SET M7 = :M7, date_time = :dateTime WHERE id = :id");

        $stmt->execute(array("M7" => $M7, "id" => $request["lid"], "dateTime" => $this->dateTime));

        if ($stmt->rowCount() > 0){
            return json_encode(array("flag" => 1, "msg" => "Data Collected"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Data Collection error"));
        }
    }
}