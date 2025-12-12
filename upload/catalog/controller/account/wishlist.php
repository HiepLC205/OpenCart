<?php
namespace Opencart\Catalog\Controller\Account;
/**
 * Class Wish List
 *
 * @package Opencart\Catalog\Controller\Account
 */
class WishList extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$data = $this->load->language('account/wishlist'); 
		$this->load->model('account/wishlist');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$this->document->setTitle($this->language->get('heading_title'));
	
		// Breadcrumbs
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language'))
		];
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
	
		$data['products'] = [];
		$results = [];
	
		if ($this->customer->isLogged()) {
			$results = $this->model_account_wishlist->getWishlist();
		} else {
			if (isset($this->session->data['wishlist'])) {
				foreach ($this->session->data['wishlist'] as $product_id) {
					$results[] = [
						'product_id' => $product_id
					];
				}
			}
		}
		foreach ($results as $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);
	
			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], 47, 47);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 47, 47);
				}
	
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}
	
				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}
	
				$data['products'][] = [
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'stock'      => $product_info['quantity'] > 0 ? $this->language->get('text_instock') : $this->language->get('text_outofstock'),
					'price'      => $price,
					'special'    => $special,
					'href'       => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id']),
					'remove'     => $this->url->link('account/wishlist|remove', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'])
				];
			}
		}
	
		$data['continue'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language'));
	
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
	
		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}
	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		$this->load->language('account/wishlist');

		// if (!$this->load->controller('account/login.validate')) {
		// 	$this->session->data['redirect'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language'));

		// 	$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		// }

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	protected function getList(): string {
		$data['cart'] = $this->url->link('common/cart.info', 'language=' . $this->config->get('config_language'));
		$data['cart_add'] = $this->url->link('checkout/cart.add', 'language=' . $this->config->get('config_language'));

		$data['products'] = [];

		// Wishlist
		$this->load->model('account/wishlist');

		// Product
		$this->load->model('catalog/product');

		// Image
		$this->load->model('tool/image');

		// Stock Status
		$this->load->model('localisation/stock_status');

		// $results = $this->model_account_wishlist->getWishlist($this->customer->getId());
		
		if ($this->customer->isLogged()) {
		
			$results = $this->model_account_wishlist->getWishlist();
		} else {
		
			$results = [];
			if (isset($this->session->data['wishlist'])) {
				foreach ($this->session->data['wishlist'] as $product_id) {
			
					$results[] = [
						'product_id' => $product_id
					];
				}
			}
		}
		

		foreach ($results as $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);

			if ($product_info) {
				if ($product_info['image'] && is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_wishlist_width'), $this->config->get('config_image_wishlist_height'));
				} else {
					$image = '';
				}

				if ($product_info['quantity'] <= 0) {
					$stock_status_id = $product_info['stock_status_id'];
				} elseif (!$this->config->get('config_stock_display')) {
					$stock_status_id = (int)$this->config->get('stock_status_id');
				} else {
					$stock_status_id = 0;
				}

				$stock_status_info = $this->model_localisation_stock_status->getStockStatus($stock_status_id);

				if ($stock_status_info) {
					$stock = $stock_status_info['name'];
				} else {
					$stock = $product_info['quantity'];
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$data['products'][] = [
					'thumb'   => $image,
					'stock'   => $stock,
					'price'   => $price,
					'special' => $special,
					'minimum' => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
					'href'    => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id']),
					'remove'  => $this->url->link('account/wishlist.remove', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'] . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''))
				] + $product_info;
			} else {
				$this->model_account_wishlist->deleteWishlist($this->customer->getId(), $result['product_id']);
			}
		}

		return $this->load->view('account/wishlist_list', $data);
	}

	/**
	 * Add
	 *
	 * @return void
	 */
	public function add(): void {
		$this->load->language('account/wishlist');
		$json = [];
		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
	
		$this->load->model('catalog/product');
	
		$product_info = $this->model_catalog_product->getProduct($product_id);
	
		if ($product_info) {
			if ($this->customer->isLogged()) {
				$this->load->model('account/wishlist');
				$results = $this->model_account_wishlist->getWishlist();
				$exists = false;
				foreach ($results as $result) {
					if ($result['product_id'] == $product_id) {
						$exists = true;
						break;
					}
				}
				if ($exists) {
					$this->model_account_wishlist->deleteWishlist($product_id);
					$json['action'] = 'remove'; 
					$json['success'] = sprintf($this->language->get('text_remove'), $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language')));
				} else {

					$this->model_account_wishlist->addWishlist($this->request->post['product_id']);
					$json['action'] = 'add'; 
					$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language')));
				}
			} 

			else {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = [];
				}
				if (in_array($this->request->post['product_id'], $this->session->data['wishlist'])) {
					$key = array_search($this->request->post['product_id'], $this->session->data['wishlist']);
					if ($key !== false) {
						unset($this->session->data['wishlist'][$key]);
					}
					
					$json['action'] = 'remove';
					$json['success'] = sprintf($this->language->get('text_remove'), $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language')));
				} else {
					$this->session->data['wishlist'][] = $this->request->post['product_id'];
					
					$json['action'] = 'add';
					$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language')));
				}
			}
			$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : ($this->customer->isLogged() ? $this->model_account_wishlist->getTotalWishlist() : 0)));
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	
	/**else
	 * Remove
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->load->language('account/wishlist');
	
		$json = [];
	
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}
	
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');
			$this->model_account_wishlist->deleteWishlist($product_id);
		} else {
			if (isset($this->session->data['wishlist'])) {
				$key = array_search($product_id, $this->session->data['wishlist']);
				if ($key !== false) {
					unset($this->session->data['wishlist'][$key]);
					$this->session->data['wishlist'] = array_values($this->session->data['wishlist']);
				}
			}
		}

		$this->session->data['success'] = $this->language->get('text_remove');
		$json['success'] = $this->language->get('text_remove');
		$json['redirect'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language'));
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	

    public function getkeys(): void {
        $this->load->model('account/wishlist');
        $json = [];
        if ($this->customer->isLogged()) {
            $results = $this->model_account_wishlist->getWishlist();
            foreach ($results as $result) {
                $json[] = $result['product_id'];
            }
        } else {
            if (isset($this->session->data['wishlist'])) {
                $json = $this->session->data['wishlist'];
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    	}

	public function dropdown(): void {
		$this->load->language('account/wishlist');
		$this->load->model('account/wishlist');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
	
		$data['products'] = [];
		$results = [];
		if ($this->customer->isLogged()) {
			$results = $this->model_account_wishlist->getWishlist();
		} else {
			if (isset($this->session->data['wishlist'])) {
				foreach ($this->session->data['wishlist'] as $product_id) {
					$results[] = ['product_id' => $product_id];
				}
			}
		}
	
		foreach ($results as $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);
	
			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], 47, 47);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 47, 47);
				}
	
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}
	
				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}
	
				$data['products'][] = [
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'name'       => $product_info['name'],
					'href'       => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id']),
					'price'      => $price,
					'special'    => $special
				];
			}
		}
	
		$data['wishlist_link'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language'));
		$this->response->setOutput($this->load->view('account/wishlist_dropdown', $data));
	}

}