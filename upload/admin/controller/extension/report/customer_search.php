<?php
class ControllerExtensionReportCustomerSearch extends Controller {
	public function index() {
		$this->load->language('extension/report/customer_search');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_customer_search', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report&language=' . $this->config->get('config_language')));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token']. '&language=' . $this->config->get('config_language'))
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report'. '&language=' . $this->config->get('config_language'))
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/report/customer_search', 'user_token=' . $this->session->data['user_token']. '&language=' . $this->config->get('config_language'))
		);

		$data['action'] = $this->url->link('extension/report/customer_search', 'user_token=' . $this->session->data['user_token']. '&language=' . $this->config->get('config_language'));

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report'. '&language=' . $this->config->get('config_language'));

		if (isset($this->request->post['report_customer_search_status'])) {
			$data['report_customer_search_status'] = $this->request->post['report_customer_search_status'];
		} else {
			$data['report_customer_search_status'] = $this->config->get('report_customer_search_status');
		}

		if (isset($this->request->post['report_customer_search_sort_order'])) {
			$data['report_customer_search_sort_order'] = $this->request->post['report_customer_search_sort_order'];
		} else {
			$data['report_customer_search_sort_order'] = $this->config->get('report_customer_search_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/customer_search_form', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/customer_search')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function report() {
		$this->load->language('extension/report/customer_search');
		
		$data['groups'] = array();

		$data['groups'][] = array(
			'text'  => $this->language->get('text_year'),
			'value' => 'year',
		);

		$data['groups'][] = array(
			'text'  => $this->language->get('text_month'),
			'value' => 'month',
		);

		$data['groups'][] = array(
			'text'  => $this->language->get('text_week'),
			'value' => 'week',
		);

		$data['groups'][] = array(
			'text'  => $this->language->get('text_day'),
			'value' => 'day',
		);

		if (isset($this->request->get['filter_date_start'])) {
			$filter_date_start = $this->request->get['filter_date_start'];
		} else {
			$filter_date_start = '';
		}

		if (isset($this->request->get['filter_date_end'])) {
			$filter_date_end = $this->request->get['filter_date_end'];
		} else {
			$filter_date_end = '';
		}

		if (isset($this->request->get['filter_keyword'])) {
			$filter_keyword = $this->request->get['filter_keyword'];
		} else {
			$filter_keyword = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_ip'])) {
			$filter_ip = $this->request->get['filter_ip'];
		} else {
			$filter_ip = '';
		}
		
		if (isset($this->request->get['filter_country_id'])) {
			$filter_country_id = $this->request->get['filter_country_id'];
		} else {
			$filter_country_id = 0;
		}
		
		if (isset($this->request->get['filter_zone_id'])) {
			$filter_zone_id = $this->request->get['filter_zone_id'];
		} else {
			$filter_zone_id = 0;
		}
		
		if (isset($this->request->get['filter_group'])) {
			$filter_group = $this->request->get['filter_group'];
		} else {
			$filter_group = 'week';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->load->model('extension/report/customer');
		
		$this->load->model('catalog/category');
		
		$this->load->model('setting/setting');
		
		$this->load->model('localisation/country');
		
		$this->load->model('localisation/zone');
		
		$data['countries'] = $this->model_localisation_country->getCountries();

		$data['searches'] = array();

		$filter_data = array(
			'filter_date_start'	=> $filter_date_start,
			'filter_date_end'	=> $filter_date_end,
			'filter_keyword'    => $filter_keyword,
			'filter_customer'   => $filter_customer,
			'filter_ip'         => $filter_ip,			
			'filter_country_id'	=> $filter_country_id,
			'filter_zone_id'	=> $filter_zone_id,
			'filter_group'		=> $filter_group,
			'start'             => ($page - 1) * 20,
			'limit'             => 20
		);

		$search_total = $this->model_extension_report_customer->getTotalCustomerSearches($filter_data);

		$results = $this->model_extension_report_customer->getCustomerSearches($filter_data);

		foreach ($results as $result) {
			$store_info = $this->model_setting_setting->getSetting('config', $result['store_id']);
			
			if ($store_info) {
				$store_id = $store_info['config_store_id'];
				
				$store_name = $store_info['config_name'];
				
				$country_info = $this->model_localisation_country->getCountry($store_info['config_country_id']);
				
				$zone_info = $this->model_localisation_zone->getZone($store_info['config_zone_id']);
			} else {
				$store_id = $this->config->get('config_store_id');
				
				$store_name = $this->config->get('config_name');
				
				$country_info = $this->model_localisation_country->getCountry($result['payment_country_id']);
				
				$zone_info = $this->model_localisation_zone->getZone($result['payment_zone_id']);
			}
			
			$data['searches'][] = array(
				'keyword'     		=> $result['keyword'],
				'products'    		=> $result['products'],				
				'recurring_status'	=> $result['recurring_status'],
				'categories'    	=> $result['categories'],
				'searches'			=> $result['searches'],
				'country'			=> $country_info,
				'zone'				=> $zone_info,
				'payment_method'	=> $result['payment_method'],
				'shipping_method'	=> $result['shipping_method'],
				'store_name'		=> $store_name,
				'store_href'		=> $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . (int)$store_id . '&language=' . $this->config->get('config_language')),
				'tax'        		=> $this->currency->format($result['tax'], $this->config->get('config_currency')),
				'total'      		=> $this->currency->format($result['total'], $this->config->get('config_currency')),
				'date_start' 		=> date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end'   		=> date($this->language->get('date_format_short'), strtotime($result['date_end'])),
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$url = '';

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_keyword'])) {
			$url .= '&filter_keyword=' . urlencode($this->request->get['filter_keyword']);
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode($this->request->get['filter_customer']);
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}
		
		if (isset($this->request->get['filter_country_id'])) {
			$url .= '&filter_country_id=' . $this->request->get['filter_country_id'];
		}
		
		if (isset($this->request->get['filter_zone_id'])) {
			$url .= '&filter_zone_id=' . $this->request->get['filter_zone_id'];
		}
		
		if (isset($this->request->get['filter_group'])) {
			$url .= '&filter_group=' . $this->request->get['filter_group'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new Pagination();
		$pagination->total = $search_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_search' . $url . '&page={page}'. '&language=' . $this->config->get('config_language'));

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($search_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($search_total - $this->config->get('config_limit_admin'))) ? $search_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $search_total, ceil($search_total / $this->config->get('config_limit_admin')));

		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_keyword'] = $filter_keyword;
		$data['filter_customer'] = $filter_customer;
		$data['filter_ip'] = $filter_ip;		
		$data['filter_country_id'] = $filter_country_id;
		$data['filter_zone_id'] = $filter_zone_id;
		$data['filter_group'] = $filter_group;

		return $this->load->view('extension/report/customer_search_info', $data);
	}
}