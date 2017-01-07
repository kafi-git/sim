<?php
namespace Home\Controller;

use Core\Controller;
use Home\Resources\Views\HomeView;

/**
 * Class HomeController
 * @package Home\Controller
 */
class HomeController extends Controller
{
    /**
     * @var HomeView
     */
    private $view;
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var string
     */
    private $cssPath;
    /**
     * @var string
     */
    private $jsPath;
    /**
     * @var string
     */
    private $vendorPath;

    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        $this->view = new HomeView();
        $this->basePath = $this->getBasePath();
        $this->cssPath = $this->getCssPath();
        $this->jsPath = $this->getJsPath();
        $this->vendorPath = $this->getVendorPath();
    }

    /**
     *
     */
    public function index()
    {
        $this->head();
        $this->body();
        $this->foot();
    }

    /**
     *
     */
    public function head()
    {
        $this->view->head($this->basePath, $this->cssPath, $this->vendorPath);
    }

    /**
     *
     */
    public function body()
    {
        $this->view->body();
    }

    /**
     *
     */
    public function foot()
    {
        $this->view->foot($this->jsPath, $this->vendorPath);
    }
}