<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/7/2017
 * Time: 2:40 AM
 */

namespace User\Resources\Views;


class ServerMasqueradingAttackView
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

                <div class="col-xs-4"><h3>Server Masquerading Attack</h3>
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

                <strong>User</strong>

            </div>
            <div class="col-xs-4"></div>
            <div class="col-xs-4" align="center">

                <strong>Attacker</strong>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <i class="fa fa-users fa-4x pointer" onclick="user_login_form();"
                   title="Click to get user login form"></i>
                <input type="hidden" name="user_id" id="user_id" value="0"/>
                <input type="hidden" name="password" id="password"/>
                <input type="hidden" name="biometric_key" id="biometric_key"/>
                <input type="hidden" name="server_id" id="server_id" value="0"/>

            </div>
            <div class="col-xs-4" align="center">

                <i class="fa fa-youtube-play fa-3x" onclick="sma_login_request_by_ci();"></i>
                <i class="fa fa-arrow-right fa-3x hidden arrow-ua"></i>

            </div>
            <div class="col-xs-4" align="center">

                <i class="fa fa-user-secret fa-4x"></i>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-4 status" align="center" id="user-status">&nbsp;</div>
            <div class="col-xs-4 status" align="center" id="process-status">&nbsp;</div>
            <div class="col-xs-4 status" align="center" id="attacker-status">&nbsp;</div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <div class="console" id="user-console"></div>

            </div>
            <div class="col-xs-4" align="center">

            </div>
            <div class="col-xs-4" align="center">

                <div class="console" id="attacker-console"></div>

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
        <div class="modal fade" tabindex="-1" role="dialog" id="modal_login_form">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">User Login Form</h4>
                    </div>
                    <div class="modal-body">
                        <form name="login_form" id="login_form">
                            <div class="form-group">
                                <label for="srf_user_id" class="control-label">User ID (IDi):</label>
                                <span id="sp_srf_user_id">

                                    <select id="srf_user_id" class="form-control"
                                            onchange="get_login_info(this.value);">

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
                                <label for="srf_password" class="control-label">password (PWi):</label>
                                <input type="text" class="form-control" id="srf_password"/>
                            </div>
                            <div class="form-group">
                                <label for="srf_biometric_key" class="control-label">Biometric Key (Bi):</label>
                                <textarea class="form-control" id="srf_biometric_key"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="srf_server_id" class="control-label">Server ID (SIDi):</label>
                                <span id="sp_srf_server_id">

                                    <select id="srf_server_id" class="form-control">

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
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="save_login_info();">Save</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <?php
    }
}