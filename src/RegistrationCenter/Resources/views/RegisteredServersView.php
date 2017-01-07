<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/24/2016
 * Time: 4:56 PM
 */

namespace RegistrationCenter\Resources\Views;


/**
 * Class RegisteredServersView
 * @package Server\Resources\Views
 */
class RegisteredServersView
{
    /**
     * @param array $data
     */
    public function body($data = array())
    {
        ?>
        <div class="container-fluid" id="main-window">


        <div class="container gap">&nbsp;</div>
        <div class="container"><h3>Registered Server List</h3><hr/></div>
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
                    <th>Server Domain</th>
                    <th>Server ID</th>
                    <th>HKs</th>

                </tr>

                <?php
                $sn = 1;

                foreach ($data as $item) {
                    $class = $sn % 2 == 0 ? "active" : "info";

                    ?>

                    <tr class="<?php echo $class; ?>">

                        <td><?php echo $sn++; ?></td>
                        <td><?php echo $item["server_domain"]; ?></td>
                        <td><?php echo $item["server_id"]; ?></td>
                        <td><?php echo $item["HKs"]; ?></td>

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