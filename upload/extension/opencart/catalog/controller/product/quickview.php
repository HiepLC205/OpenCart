<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Product;

class Quickview extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('product/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			$data['heading_title'] = $product_info['name'];
			$data['product_id'] = $product_id;
			$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			$data['description'] = mb_substr(trim(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))), 0, 150) . '..';
			$data['image'] = $this->model_tool_image->resize($product_info['image'] ?: 'placeholder.png', 400, 400);
			
			// Lấy Options sản phẩm
			$data['options'] = [];
			foreach ($this->model_catalog_product->getOptions($product_id) as $option) {
				$item_option_value = [];
				foreach ($option['product_option_value'] as $option_value) {
					$item_option_value[] = [
						'product_option_value_id' => $option_value['product_option_value_id'],
						'name'                    => $option_value['name'],
						'price'                   => (float)$option_value['price'] ? $this->currency->
						format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : false,
						'price_prefix'            => $option_value['price_prefix']
					];
				}
				$data['options'][] = [
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $item_option_value,
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				];
			}

			$data['product_link'] = $this->url->link('product/product', 'product_id=' . $product_id);
			$data['cart_link'] = $this->url->link('checkout/cart');

			$this->response->setOutput($this->load->view('extension/opencart/product/quickview', $data));
		}
	}
}