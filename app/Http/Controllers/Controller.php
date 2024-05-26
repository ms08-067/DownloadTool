<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Data pass to view
     * @var array $data
     */
    protected $data = [];

    /**
     * Data pass to js
     * @var array $js
     */
    protected $js = [];

    /**
     * @var object Monolog
     */
    protected $log;

    /**
     * Render a template with data
     *
     * @author tolawho
     * @param string $tpl
     * @return \Illuminate\View\View
     */
    protected function render($tpl)
    {
        $this->data['locale'] = $this->js['locale'] = app()->getLocale();
        $this->data['currency'] = $this->js['currency'] = \Session::get('appcurrency');
        return view($tpl, $this->data)->with('js', $this->js);
    }

}
