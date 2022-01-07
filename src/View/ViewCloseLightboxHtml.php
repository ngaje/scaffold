<?php
namespace Ngaje\Scaffold\View;

class ViewCloseLightboxHtml extends ViewBase
{
    public function renderCloseLightbox($js_function = '', $parameters = array())
    {
        ?>
        <script type="text/javascript">
            TINYBOX.close();
            <?php if ($js_function) {
                echo $js_function . '(' . implode(",", $parameters) . ')';
            } ?>
        </script>
        <?php
    }
}
