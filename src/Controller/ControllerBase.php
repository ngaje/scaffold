<?php
namespace Netshine\Scaffold\Controller;

use Netshine\Scaffold\ICms;
use Netshine\Scaffold\Request;
use Netshine\Scaffold\Url;
use Netshine\Scaffold\View\ViewNotFoundHtml;
use Netshine\Scaffold\View\ViewBase;
use Netshine\Scaffold\View\Form\FormBase;
use Netshine\Scaffold\Model\ServiceBase;
use Netshine\Scaffold\View\Form\FormMapper;

abstract class ControllerBase implements IController
{
    /** @var ICms **/
    protected $cms;
    /** @var Request **/
    protected $request;
    /** @var ViewBase **/
    protected $view;
    /** @var FormBase **/
    protected $form;
    /** @var FormMapper **/
    protected $form_mapper;
    /** @var ServiceBase **/
    protected $service;
    /** @var int **/
    protected $user_id;
    /** @var array **/
    protected $filters = array();

    use \Netshine\Scaffold\TResource;

    public function __construct(ICms $cms, Request $request, ViewBase $view = null, FormBase $form = null, ServiceBase $service = null)
    {
        $this->cms = $cms;
        $this->request = $request;
        $this->view = $view;
        $this->form = $form;
        $this->service = $service;
        $this->user_id = $cms->getCurrentUserId();
        if ($this->form && !$this->form->record_id) {
            $record_id = $this->request->getRequestParam('id');
            $this->form->record_id = $record_id == 'new' ? $record_id : intval($record_id);
        }
        $this->filters = $this->request->getRequestFilters();
        if ($this->view) {
            $this->view->filters = $this->filters;
        }
        $this->preserveFilters();
        $this->initialise();
    }

    public function initialise() {}

    protected function preserveFilters()
    {
        if (isset($this->filters) && isset($this->form)) {
            foreach ($this->filters as $key=>$value)
            {
                $this->form->preserved_filters[filter_var($key, FILTER_SANITIZE_STRING)] = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }
    }

    public function setFormMapper(FormMapper $mapper)
    {
        $this->form_mapper = $mapper;
    }

    public function executeMethod()
    {
        try {
            $method = substr($this->request->method, 0, 1) . substr(str_replace(' ', '', ucwords(str_replace('_', ' ', $this->request->method))), 1);
            if (method_exists($this, $method)) {
                $this->$method();
            } else {
                $this->get();
            }
        }
        catch (\Exception $ex)
        {
            //TODO: Something more graceful! Log it somewhere, and show a nice error message

            $this->recordNotFound(null, $ex->getMessage());
            return;

            //die($ex->getMessage());
        }
    }

    abstract public function get();

    public function sanitiseIdArray($id_array)
    {
        if (is_array($id_array)) {
            for ($i=0; $i<count($id_array); $i++) {
                $id_array[$i] = intval($id_array[$i]);
            }
        }
        return $id_array;
    }

    protected function redirectToPage($url)
    {
        //Clear the buffers
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15) {
                break;
            }
        }

        if (strpos($url, '[return]') !== false) {
            $url = str_replace('[return]', $this->form->return_url, $url);
        }

        //Do the redirect
        if (!headers_sent()) {
            header('Location: ' . $url, true, 303);
        } else { //Should never happen!
            echo "<script type=\"text/javascript\">window.location='$url';</script>";
        }
        exit;
    }

    protected function recordNotFound($return_url = '', $error_message = '')
    {
        $view = new ViewNotFoundHtml($this->cms, $this->request->language);
        $view->renderNotFound($return_url, $error_message);
    }

    protected function ajaxSuccess($payload = '')
    {
        echo 'OK';
        if (strlen($payload) > 0) {
            echo ':' . $payload;
        }
        exit;
    }

    protected function ajaxFailure($message = '')
    {
        if (strlen($message) == 0) {
            $message = $this->request->language->error['err_ajax_failure'];
        }
        echo $message;
        exit;
    }

    protected function clearOutputBuffers()
    {
        $loop_counter = 0;
        while(ob_get_level()) {
            if ($loop_counter > 10) {
                break;
            }
            ob_end_clean();
            $loop_counter++;
        }
    }
}