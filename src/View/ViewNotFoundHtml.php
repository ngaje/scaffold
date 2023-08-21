<?php
namespace Ngaje\Scaffold\View;

class ViewNotFoundHtml extends ViewBase
{
    public function renderNotFound($return_link = '', $error_message = '')
    {
        if (!headers_sent()) {
            header(@$_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
        }
        if (!$error_message) {
            $error_message = $this->language->error['err_page_not_found'];
            if (strlen($error_message) == 0) {
                $error_message = 'Sorry, that page could not be found!';
            }
        }

        $return_link_text = $this->language->error['err_page_not_found_go_back'];
        if (!$return_link_text) {
            $return_link_text = 'Go Back';
        }

        ?>
        <div class="system-message">
            <?php
            echo $error_message;
            ?><br /><br /><?php
            if (!$return_link) {
                ?>
                <a href="<?php echo $return_link; ?>"><?php echo $return_link_text; ?></a>
                <?php
            } else {
                ?>
                <script type="text/javascript">
                    document.write('<a href="javascript:history.go(-1);"><?php echo $return_link_text; ?></a>');
                </script>
                <?php
            }
            ?>
        </div>
        <?php
    }
}
