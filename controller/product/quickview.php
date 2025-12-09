<?php
namespace Opencart\Catalog\Controller\Product;

class Quickview extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('product/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$product_id = (int)($this->request->get['product_id'] ?? 0);

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if (!$product_info) {
			$this->response->setOutput(json_encode(['error' => 'Product not found']));
			return;
		}

		// Image
		if ($product_info['image']) {
			$image = $this->model_tool_image->resize($product_info['image'], 500, 500);
		} else {
			$image = $this->model_tool_image->resize('placeholder.png', 500, 500);
		}

		// Price
		if ($product_info['price']) {
			$price = $this->currency->format($product_info['price'], $this->session->data['currency']);
		} else {
			$price = false;
		}

		// Special
		if ($product_info['special']) {
			$special = $this->currency->format($product_info['special'], $this->session->data['currency']);
		} else {
			$special = false;
		}

		// Options
		$options = $this->model_catalog_product->getOptions($product_id);

		$data = [
			'product_id' => $product_id,
			'name'       => $product_info['name'],
			'image'      => $image,
			'price'      => $price,
			'special'    => $special,
			'description'=> html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'),
			'options'    => $options,
			'minimum'    => $product_info['minimum'] ?? 1,
			// Cart add endpoint and labels so quickview can post to the same URL as product thumbs
			'cart_add'   => $this->url->link('checkout/cart.add'),
			'cart'       => $this->url->link('common/cart'),
			'button_cart' => $this->language->get('button_cart') ?? 'Add to Cart'
		];

		$this->response->setOutput($this->load->view('product/quickview', $data));
	}
}
