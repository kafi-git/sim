<?php
namespace Server\Resources\Views;

/**
 * Class RegistrationView
 * @package Server\Resources\Views
 */
/**
 * Class RegistrationView
 * @package Server\Resources\Views
 */
class RegistrationView
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

                <div class="col-xs-4"><h3>Server Registration Phase</h3>
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

                <strong>Server</strong>

            </div>
            <div class="col-xs-4">


            </div>
            <div class="col-xs-4" align="center">

                <strong>Registration Center</strong>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <i class="fa fa-server fa-4x pointer"  onclick="server_registration_form();" title="Click to get server registration form"></i>
                <input type="hidden" name="server_id" id="server_id" value="0" />

            </div>
            <div class="col-xs-4" align="center">

                <i class="fa fa-youtube-play fa-3x" onclick="registration_request_by_si();"></i>
                <i class="fa fa-arrow-right fa-3x hidden"></i>

            </div>
            <div class="col-xs-4" align="center">

                <i class="fa fa-institution fa-4x"></i>

            </div>

        </div>
        <div class="row">

            <div class="col-xs-4 status" align="center" id="server-status">&nbsp;</div>
            <div class="col-xs-4 status" align="center" id="process-status">&nbsp;</div>
            <div class="col-xs-4 status" align="center" id="rc-status">&nbsp;</div>

        </div>
        <div class="row">

            <div class="col-xs-4" align="center">

                <div class="console" id="server-console"></div>

            </div>
            <div class="col-xs-4" align="center">


            </div>
            <div class="col-xs-4" align="center">

                <div class="console" id="rc-console"></div>

            </div>

        </div>


        <?php
    }


    /**
     * @param array $servers
     */
    public function body_bottom($servers = array())
    {
        ?>

        </div>

        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="modal_server_form" >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Server Registration Form</h4>
                    </div>
                    <div class="modal-body">
                        <form name="server_form" id="server_form">
                            <div class="form-group">
                                <label for="srf_server_id" class="control-label">Server ID (SIDi):</label>
                                <span id="sp_srf_server_id">

                                    <select id="srf_server_id" class="form-control">

                                        <option value="0">Select</option>
                                        <?php
                                        foreach ($servers as $server) {
                                            ?>
                                            <option value="<?php echo $server["server_id"]; ?>"><?php echo $server["server_domain"]." [".$server["server_id"]."]"; ?></option>
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
                        <button type="button" class="btn btn-primary" onclick="save_server_info();">Save</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <?php
    }
}