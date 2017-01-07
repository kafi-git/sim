<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/5/2017
 * Time: 5:13 PM
 */

namespace User\Resources\Views;


/**
 * Class PasswordChangeView
 * @package User\Resources\Views
 */
class PasswordChangeView
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

                <div class="col-xs-4"><h3>Password Change Phase</h3>
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

            <div class="col-xs-3" align="center">

                <strong>User</strong>

            </div>
            <div class="col-xs-1"></div>
            <div class="col-xs-3" align="center">

                <strong>Registration Center</strong>

            </div>
            <div class="col-xs-1"></div>
            <div class="col-xs-3" align="center">

                <strong>Server</strong>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-3" align="center">

                <i class="fa fa-users fa-4x pointer" onclick="password_change_form();"
                   title="Click to get user registration form"></i>
                <input type="hidden" name="user_id" id="user_id" value="0"/>
                <input type="hidden" name="password" id="password"/>
                <input type="hidden" name="recovery_contact" id="recovery_contact"/>
                <input type="hidden" name="biometric_key" id="biometric_key"/>
                <input type="hidden" name="server_id" id="server_id" value="0"/>
                <input type="hidden" name="password_new" id="password_new"/>

            </div>
            <div class="col-xs-1" align="center">

                <i class="fa fa-youtube-play fa-3x" onclick="password_change_request_by_ci();"></i>
                <i class="fa fa-arrow-right fa-3x hidden arrow-ur"></i>

            </div>
            <div class="col-xs-3" align="center">

                <i class="fa fa-institution fa-4x"></i>

            </div>
            <div class="col-xs-1" align="center">

                <i class="fa fa-arrow-right fa-3x hidden arrow-rs"></i>

            </div>
            <div class="col-xs-3" align="center">

                <i class="fa fa-server fa-4x"></i>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-3 status" align="center" id="user-status">&nbsp;</div>
            <div class="col-xs-1 status" align="center" id="ur-process-status">&nbsp;</div>
            <div class="col-xs-3 status" align="center" id="rc-status">&nbsp;</div>
            <div class="col-xs-1 status" align="center" id="rs-process-status">&nbsp;</div>
            <div class="col-xs-3 status" align="center" id="server-status">&nbsp;</div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <div class="console" id="user-console"></div>

            </div>
            <div class="col-xs-4" align="center">

                <div class="console" id="rc-console"></div>

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
        <div class="modal fade" tabindex="-1" role="dialog" id="modal_pc_form">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Password Change Form</h4>
                    </div>
                    <div class="modal-body">
                        <form name="pc_form" id="pc_form">
                            <div class="form-group">
                                <label for="pcf_user_id" class="control-label">User ID (IDi):</label>
                                <span id="sp_pcf_user_id">

                                    <select id="pcf_user_id" class="form-control"
                                            onchange="get_user_info_pc(this.value);">

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
                                <label for="pcf_password" class="control-label">password (PWi):</label>
                                <input type="text" class="form-control" id="pcf_password"/>
                            </div>
                            <div class="form-group">
                                <label for="pcf_recovery_contact" class="control-label">Recovery Contact
                                    (Rcont):</label>
                                <input type="text" class="form-control" id="pcf_recovery_contact"/>
                            </div>
                            <div class="form-group">
                                <label for="pcf_biometric_key" class="control-label">Biometric Key (Bi):</label>
                                <textarea class="form-control" id="pcf_biometric_key"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="pcf_server_id" class="control-label">Server ID (SIDi):</label>
                                <span id="sp_pcf_server_id">

                                    <select id="pcf_server_id" class="form-control">

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
                                <label for="pcf_password_new" class="control-label">New password (PWni):</label>
                                <input type="text" class="form-control" id="pcf_password_new"/>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="save_user_info_pc();">Save</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <?php
    }
}