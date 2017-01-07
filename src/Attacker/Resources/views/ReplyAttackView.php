<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/6/2017
 * Time: 11:09 PM
 */

namespace Attacker\Resources\Views;


/**
 * Class ReplyAttackView
 * @package Attacker\Resources\Views
 */
class ReplyAttackView
{
    /**
     *
     */
    public function body()
    {
        ?>
        <div class="container-fluid" id="main-window">

        <div class="container">

            <div class="row">

                <div class="col-xs-4"><h3>Reply Attack</h3>
                    <hr/>
                </div>
                <div class="col-xs-8"></div>

            </div>

        </div>
        <div class="container">

        <div class="row">

            <div class="col-xs-12 gap" align="center">&nbsp;</div>


        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <strong>Attacker</strong>

            </div>
            <div class="col-xs-4"></div>
            <div class="col-xs-4" align="center">

                <strong>Server</strong>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <i class="fa fa-user-secret fa-4x pointer" onclick="show_reply_attack_form();"
                   title="Click to get reply attack form"></i>
                <input type="hidden" name="user_id" id="user_id" value="0"/>
                <input type="hidden" name="server_id" id="server_id" value="0"/>
                <input type="hidden" name="m1" id="m1" value=""/>
                <input type="hidden" name="m2" id="m2" value=""/>

            </div>
            <div class="col-xs-4" align="center">

                <i class="fa fa-youtube-play fa-3x" onclick="reply_attack_login_request_by_ai();"></i>
                <i class="fa fa-arrow-right fa-3x hidden arrow-as"></i>

            </div>
            <div class="col-xs-4" align="center">

                <i class="fa fa-server fa-4x"></i>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-4 status" align="center" id="attacker-status">&nbsp;</div>
            <div class="col-xs-4 status" align="center" id="process-status">&nbsp;</div>
            <div class="col-xs-4 status" align="center" id="server-status">&nbsp;</div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <div class="console" id="attacker-console"></div>

            </div>
            <div class="col-xs-4" align="center">

            </div>
            <div class="col-xs-4" align="center">

                <div class="console" id="server-console"></div>

            </div>

        </div>


        <?php
    }

    /**
     * @param $users
     * @param $servers
     */
    public function body_bottom($users, $servers)
    {
        ?>

        </div>

        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="modal_reply_attack_form">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Reply Attack Form</h4>
                    </div>
                    <div class="modal-body">
                        <form name="reply_attack_form" id="reply_attack_form">
                            <div class="form-group">
                                <label for="raf_user_id" class="control-label">User ID (IDi):</label>
                                <span id="sp_raf_user_id">

                                    <select id="raf_user_id" class="form-control"
                                            onchange="get_m1m2_set();">

                                        <option value="0">Select</option>

                                        <?php
                                        foreach ($users as $user) {
                                            ?>
                                            <option value="<?php echo $user["user_id"]; ?>"><?php echo $user["user_id"]; ?></option>
                                            <?php
                                        }
                                        ?>

                                    </select>

                                </span>
                            </div>
                            <div class="form-group">
                                <label for="raf_server_id" class="control-label">Server ID (SIDi):</label>
                                <span id="sp_raf_server_id">

                                    <select id="raf_server_id" class="form-control"
                                            onchange="get_m1m2_set();">

                                        <option value="0">Select</option>
                                        <?php
                                        foreach ($servers as $server) {
                                            ?>
                                            <option value="<?php echo $server["server_id"]; ?>"><?php echo $server["server_domain"] . " [" . $server["server_id"] . "]"; ?></option>
                                            <?php
                                        }
                                        ?>

                                    </select>

                                </span>
                            </div>
                            <div class="form-group">
                                <label for="raf_m1_m2" class="control-label">M1-M2 Set:</label>
                                <span id="sp_raf_m1_m2">

                                    <select id="raf_m1_m2" class="form-control"
                                        onchange="get_m1_m2(this.value);">

                                        <option value="0">Select</option>

                                    </select>

                                </span>
                            </div>
                            <div class="form-group">
                                <label for="raf_m1" class="control-label">M1:</label>
                                <span id="sp_raf_m1">

                                    <input type="text" class="form-control" id="raf_m1" />

                                </span>
                            </div>
                            <div class="form-group">
                                <label for="raf_m2" class="control-label">M2:</label>
                                <span id="sp_raf_m2">

                                    <input type="text" class="form-control" id="raf_m2" />

                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="save_reply_attack_info();">Save</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <?php
    }


    public function m1m2($m1m2_list = array())
    {
    ?>
        <select id="raf_m1_m2" class="form-control"
                onchange="get_m1_m2(this.value);">

            <option value="0">Select</option>
            <?php
            if (count($m1m2_list) > 0){
                foreach ($m1m2_list as $id){
                ?>
                    <option value="<?php echo $id; ?>">M1-M2 Set <?php echo $id; ?></option>

                    <?php
                }
            }
            ?>

        </select>
        <?php
    }
}