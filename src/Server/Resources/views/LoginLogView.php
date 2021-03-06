<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/4/2017
 * Time: 12:44 PM
 */

namespace Server\Resources\Views;


/**
 * Class LoginLogView
 * @package Server\Resources\Views
 */
class LoginLogView
{
    /**
     * @param array $data
     */
    public function body($data = array())
    {
        ?>
        <div class="container-fluid" id="main-window">


        <div class="container gap">&nbsp;</div>
        <div class="container"><h3>Login Log</h3>
            <hr/>
        </div>
        <div class="container" id="list">


        <?php
        $this->dataList($data);
    }

    /**
     * @param array $data
     */
    public function dataList($data = array())
    {
        if (!empty($data)) {
            ?>

            <table class="table table-bordered">

                <tr class="active">

                    <th>S.N.</th>
                    <th>User ID</th>
                    <th>Server Domain</th>
                    <th>Server ID</th>
                    <th>Time</th>

                </tr>

                <?php
                $sn = 1;

                foreach ($data as $item) {
                    $class = $sn % 2 == 0 ? "active" : "info";

                    ?>

                    <tr class="<?php echo $class; ?>">

                        <td><?php echo $sn++; ?></td>
                        <td><?php echo $item["user_id"]; ?></td>
                        <td><?php echo $item["server_domain"]; ?></td>
                        <td><?php echo $item["server_id"]; ?></td>
                        <td><?php echo $item["date_time"]; ?></td>

                    </tr>

                    <?php
                }
                ?>


            </table>

            <?php
        }
    }

    /**
     *
     */
    public function body_bottom()
    {
        ?>
        </div>

        </div>
        <?php
    }
}