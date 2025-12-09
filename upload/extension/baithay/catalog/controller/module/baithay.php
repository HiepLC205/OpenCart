<?php
namespace Opencart\Catalog\Controller\Extension\Baithay\Module;

class Baithay extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['products'] = [];
		$data['config_language'] = $this->config->get('config_language');

		// Lấy cấu hình từ Admin
		$setting_products = $this->config->get('module_baithay_product');
		$limit = (int)$this->config->get('module_baithay_limit');
		$width = (int)$this->config->get('module_baithay_width');
		$height = (int)$this->config->get('module_baithay_height');

		if (!$limit) $limit = 5;
		if (!$width) $width = 200;
		if (!$height) $height = 200;

		if (!empty($setting_products)) {
			$products = array_slice($setting_products, 0, $limit);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $width, $height);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					$data['products'][] = [
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'price'       => $price,
						'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'])
					];
				}
			}
		}

		// Trả về giao diện (Nếu không có sản phẩm cũng trả về chuỗi rỗng để không lỗi)
		if ($data['products']) {
			return $this->load->view('extension/baithay/module/baithay', $data);
		} else {
			return '';
		}
	}
}