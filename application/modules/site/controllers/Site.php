<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @package    Codeigniter
 * @subpackage Site
 * @category   HMVC Controller
 * @author     Agung Dirgantara <agungmasda29@gmail.com>
 */

class Site extends HMVC_Controller
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Home
	 */
	public function index()
	{
		$this->template->load('index');
	}

	/**
	 * Show error pages
	 *
	 * @param      int     $code     Code
	 * @param      string  $header   Header
	 * @param      string  $message  Message
	 */
	public function show_error($code = 404, $header = 'Not found', $message = 'Page not found')
	{
		$data['code'] = $code;
		$data['header'] = $header;
		$data['message'] = $message;
		$this->output->set_header('HTTP/1.1 '.$code);
		$this->template->load('error', $data);
	}
}

/* End of file Site.php */
/* Location : ./site/controllers/Site.php */
