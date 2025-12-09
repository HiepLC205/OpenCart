<?php
namespace Opencart\Admin\Controller\Extension\Baithay\Module;

class Baithay extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/baithay/module/baithay');
		$this->document->setTitle($this->language->get('heading_title'));

		 

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/baithay/module/baithay', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/baithay/module/baithay|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		$data['module_baithay_status'] = $this->config->get('module_baithay_status');
		$data['module_baithay_limit'] = $this->config->get('module_baithay_limit');
		$data['module_baithay_width'] = $this->config->get('module_baithay_width');
		$data['module_baithay_height'] = $this->config->get('module_baithay_height');

		$this->load->model('catalog/product');
		$data['products'] = [];

		$products = $this->config->get('module_baithay_product');
		if (is_array($products)) {
			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);
				if ($product_info) {
					$data['products'][] = [
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					];
				}
			}
		}

		// Truyền token sang View
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/baithay/module/baithay', $data));
	}

	public function save(): void {
		$this->load->language('extension/baithay/module/baithay');
		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/baithay/module/baithay')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_baithay', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
	public function install(): void {
		if ($this->user->hasPermission('modify', 'extension/baithay/module/baithay')) {
		}
	}
}