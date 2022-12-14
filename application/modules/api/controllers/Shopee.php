<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @package    Codeigniter
 * @subpackage Shopee
 * @category   HMVC Controller
 * @author     Agung Dirgantara <agungmasda29@gmail.com>
 */

class Shopee extends HMVC_Controller
{
	private $url;

	private $curl;

	private $image_hostname;

	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->url = 'https://shopee.co.id';
		$this->curl = new Curl\Curl;
		$this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);
		$this->image_hostname = 'https://cf.shopee.co.id';
	}

	public function search_hint($search_type = 0, $version = 1)
	{
		$request = $this->curl->get($this->url.'/api/v4/search/search_hint', array(
			'keyword' => $this->input->get('keyword'),
			'search_type' => $search_type,
			'version' => $version
		));

		$this->output->set_content_type('application/json')->set_output($request->response);
	}

	public function search_user($limit = 6, $offset = 0, $page = 'search_user', $with_search_cover = TRUE)
	{
		$request_params = array_merge(array(
			'keyword' => $this->input->get('keyword'),
			'page' => $page,
			'limit' => $limit,
			'offset' => $offset,
			'with_search_cover' => filter_var($with_search_cover, FILTER_VALIDATE_BOOLEAN)
		), $this->input->get());

		if ($response_cached = $this->cache->file->get('search_user_'.$this->input->get('keyword').'_'.$limit.'_'.$request_params['offset']))
		{
			$request = json_decode($response_cached);

			$response = $request;
		}
		else
		{
			$request = $this->curl->get($this->url.'/api/v4/search/search_user', $request_params);

			$this->cache->file->save('search_user_'.$this->input->get('keyword').'_'.$limit.'_'.$request_params['offset'], json_encode((object) array('response' => $request->response)), 10000000000);
		}

		$this->output->set_content_type('application/json')->set_output($request->response);
	}

	public function get_shop_detail($username = NULL, $sort_sold_out = 0)
	{
		if ($response_cached = $this->cache->file->get('get_shop_detail_'.$username))
		{
			$request = json_decode($response_cached);

			$response = $request;
		}
		else
		{
			$request = $this->curl->get($this->url.'/api/v4/shop/get_shop_detail', array(
				'username' => $username,
				'sort_sold_out' => $sort_sold_out
			));

			$this->cache->file->save('get_shop_detail_'.$username, json_encode((object) array('response' => $request->response)), 10000000000);
		}

		$this->output->set_content_type('application/json')->set_output($request->response);
	}

	public function get_shop_categories($shopid = NULL, $limit = 20, $offset = 0)
	{
		$request = $this->curl->get($this->url.'/api/v4/shop/get_categories', array(
			'shopid' => $shopid,
			'limit' => $limit,
			'offset' => $offset
		));

		$this->output->set_content_type('application/json')->set_output($request->response);
	}

	public function image($uid)
	{
		if (!empty($uid))
		{
			$image_cache = APPPATH.'cache'.DIRECTORY_SEPARATOR.'image_'.$uid;

			if (file_exists($image_cache))
			{
				$this->output->set_content_type('image/jpeg')->set_output(file_get_contents($image_cache));
			}
			else
			{
				$request = $this->curl->get($this->image_hostname.'/file/'.$uid.'_tn');
				file_put_contents($image_cache, $request->response);
				$this->output->set_content_type($request->response_headers[2])->set_output($request->response);
			}
		}
	}

	public function store_product($shopid = NULL, $limit = 30, $offset = 0)
	{
		$request_params = array_merge(array(
			'bundle' => 'shop_page_category_tab_main',
			'shopid' => $shopid,
			'sort_type' => 2,
			'tab_name' => 'popular',
			'limit' => $limit,
			'offset' => $offset
		), $this->input->get());

		if ($response_cached = $this->cache->file->get('store_product_'.$shopid.'_'.$limit.'_'.$offset))
		{
			$request = json_decode($response_cached);

			$response = json_decode($request);
		}
		else
		{
			$request = $this->curl->get($this->url.'/api/v4/recommend/recommend', $request_params);

			$response = json_decode($request->response);

			$this->cache->file->save('store_product_'.$shopid.'_'.$limit.'_'.$offset, json_encode($request->response), 10000000000);
		}

		foreach ($response->data->sections as $section)
		{
			if (isset($section->data) && isset($section->data->item))
			{
				foreach ($section->data->item as $item)
				{
					$product = $this->product->get_where(array('shop_id' => $item->shopid, 'item_id' => $item->itemid));

					if ($product->num_rows() < 1)
					{
						$this->product->insert(array('shop_id' => $item->shopid, 'item_id' => $item->itemid, 'data' => json_encode($item)));
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_item_ratings($shopid = NULL, $itemid = NULL, $type = 0, $filter = 0, $flag = 1, $limit = 50, $offset = 0)
	{
		$request_params = array(
			'shopid' => $shopid,
			'itemid' => $itemid,
			'type' => $type,
			'filter' => $filter,
			'flag' => $flag,
			'limit' => $limit,
			'offset' => $offset
		);

		$request_params = array_merge($request_params, $this->input->get());

		if ($response_cached = $this->cache->file->get('get_item_ratings_'.$request_params['shopid'].'_'.$request_params['itemid'].'_'.$request_params['limit'].'_'.$request_params['offset']))
		{
			$request = json_decode($response_cached);

			$response = json_decode($request->response);
		}
		else
		{
			$request = $this->curl->get($this->url.'/api/v2/item/get_ratings', $request_params);

			$response = json_decode($request->response);

			$this->cache->file->save('get_item_ratings_'.$request_params['shopid'].'_'.$request_params['itemid'].'_'.$request_params['limit'].'_'.$request_params['offset'], json_encode((object) array('response' => $request->response)), 10000000000);
		}

		if (isset($response->data) && isset($response->data->ratings))
		{
			foreach ($response->data->ratings as $rating)
			{
				$comment = $this->comment->get_where(array('order_id' => $rating->orderid));

				if ($comment->num_rows() < 1000)
				{
					if ($comment->num_rows() < 1)
					{
						$this->comment->insert(array(
							'shop_id' => $rating->shopid,
							'item_id' => $rating->itemid,
							'order_id' => $rating->orderid,
							'comment' => $rating->comment
						));

						$comment_id = $this->db->insert_id();

						if ($this->data_training->count_all_results() < 800)
						{
							$this->data_training->insert(array(
								'comment_id' => $comment_id,
								'classification' => 'Positif',
								'text' => $rating->comment
							));
						}
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output($request->response);
	}
}

/* End of file Shopee.php */
/* Location : ./api/controllers/Shopee.php */
