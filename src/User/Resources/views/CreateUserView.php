<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/24/2016
 * Time: 10:38 PM
 */

namespace User\Resources\Views;


/**
 * Class CreateUserView
 * @package User\Resources\Views
 */
class CreateUserView
{
    /**
     * @param string $uniqueBioKey
     */
    public function body($uniqueBioKey = "")
    {
        ?>
        <div class="container-fluid" id="main-window">

        <div class="container">

            <form method="post" accept-charset="utf-8" action="/?route=create-user/add">
                <div class="form-group">
                    <label for="user_id">User ID (IDi)</label>
                    <input type="text" class="form-control" id="user_id" placeholder="User ID">
                </div>
                <div class="form-group">
                    <label for="password">Password (PWi)</label>
                    <input type="text" class="form-control" id="password" placeholder="Password">
                </div>
                <div class="form-group">
                    <label for="recovery_contact">Recovery Contact (Rcont)</label>
                    <input type="text" class="form-control" id="recovery_contact" placeholder="Recovery Contact">
                </div>
                <div class="form-group">
                    <label for="biometric_key">Biometric Key (Bi)</label>
                    <textarea class="form-control" id="biometric_key"><?php echo $uniqueBioKey; ?></textarea>
                </div>
                <button class="btn btn-default" type="button" onclick="add_user();">Submit</button>
            </form>

        </div>
        <div class="container gap">&nbsp;</div>
        <div class="container" id="list">


        <?php
    }

    /**
     * @param array $data
     * @param int $id
     */
    public function dataList($data = array(), $id = 0)
    {
        if (!empty($data)) {
            ?>

            <table class="table table-bordered">

                <tr class="active">

                    <th>S.N.</th>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Password</th>
                    <th>Recovery Contact</th>
                    <th>Biometric Key</th>
                    <th>Action</th>

                </tr>

                <?php
                $sn = 1;

                foreach ($data as $item) {
                    $class = $sn % 2 == 0 ? "active" : "info";
                    $class = $item['id'] == $id ? "bg-primary" : $class;

                    ?>

                    <tr class="<?php echo $class; ?>">

                        <td><?php echo $sn++; ?></td>
                        <td><?php echo $item["id"]; ?></td>
                        <td><?php echo $item["user_id"]; ?></td>
                        <td><?php echo $item["password"]; ?></td>
                        <td><?php echo $item["recovery_contact"]; ?></td>
                        <td><?php echo $item["biometric_key"]; ?></td>
                        <td><a onclick="_delete_user('?route=create-user/delete', <?php echo $item['id']; ?>);">Delete</a>
                        </td>

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