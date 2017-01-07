<?php
namespace Server\Resources\Views;

/**
 * Class CreateServerView
 * @package Server\Resources\Views
 */
class CreateServerView
{
    /**
     * @param string $uniqueString
     */
    public function body($uniqueString = "", $key = "")
    {
        ?>
        <div class="container-fluid" id="main-window">

        <div class="container">

            <form method="post" accept-charset="utf-8" action="/?route=create-server/add">
                <div class="form-group">
                    <label for="server_domain">Domain</label>
                    <input type="text" class="form-control" id="server_domain" placeholder="Server Domain">
                </div>
                <div class="form-group">
                    <label for="server_ip">IP</label>
                    <input type="text" class="form-control" id="server_ip" placeholder="Server IP">
                </div>
                <div class="form-group">
                    <label for="server_id">Server ID (SIDi)</label>
                    <input type="text" class="form-control" id="server_id" placeholder="Server ID"
                           value="<?php echo $uniqueString; ?>">
                </div>
                <div class="form-group">
                    <label for="private_key">Servers Private Key</label>
                    <input type="text" class="form-control" id="private_key" placeholder="Servers Private Key"
                           value="<?php echo $key; ?>">
                </div>
                <button class="btn btn-default" type="button" onclick="add_server();">Submit</button>
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
                    <th>Server Domain</th>
                    <th>Server IP</th>
                    <th>Server ID</th>
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
                        <td><?php echo $item["server_domain"]; ?></td>
                        <td><?php echo $item["server_ip"]; ?></td>
                        <td><?php echo $item["server_id"]; ?></td>
                        <td><a onclick="_delete('?route=create-server/delete', <?php echo $item['id']; ?>);">Delete</a>
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