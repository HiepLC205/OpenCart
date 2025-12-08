<?php
namespace Opencart\Catalog\Model\Catalog;
/**
 * Class Review
 *
 * Can be called using $this->load->model('catalog/review');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Review extends \Opencart\System\Engine\Model {
	/**
	 * Add Review
	 *
	 * Create a new review record in the database.
	 *
	 * @param int                  $product_id primary key of the product record
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return int
	 */
	public function addReview(int $product_id, array $data): int {
		// Thêm review vào bảng review
		$this->db->query("INSERT INTO `" . DB_PREFIX . "review`
			SET `author` = '" . $this->db->escape($data['author']) . "',
				`customer_id` = '" . (int)$this->customer->getId() . "',
				`product_id` = '" . (int)$product_id . "',
				`text` = '" . $this->db->escape($data['text']) . "',
				`rating` = '" . (int)$data['rating'] . "',
				`status` = '1',
				`date_added` = NOW(),
				`date_modified` = NOW()");
		
		$review_id = $this->db->getLastId();
		
		// Thêm images nếu có
		if (!empty($data['images']) && is_array($data['images'])) {
			foreach ($data['images'] as $sort_order => $image) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "review_media` SET 
					`review_id` = '" . (int)$review_id . "',
					`type` = 'image',
					`file_path` = '" . $this->db->escape($image) . "',
					`sort_order` = '" . (int)$sort_order . "',
					`date_added` = NOW()");
			}
		}

		// Thêm videos nếu có
		if (!empty($data['videos']) && is_array($data['videos'])) {
			foreach ($data['videos'] as $sort_order => $video) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "review_media` SET 
					`review_id` = '" . (int)$review_id . "',
					`type` = 'video',
					`file_path` = '" . $this->db->escape($video) . "',
					`sort_order` = '" . (int)$sort_order . "',
					`date_added` = NOW()");
			}
		}

		return $review_id;
	}

	/**
	 * Get Review Media
	 *
	 * Get all media files (images/videos) for a review
	 *
	 * @param int $review_id primary key of the review record
	 *
	 * @return array<int, array<string, mixed>> media records
	 */
	public function getReviewMedia(int $review_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "review_media` 
			WHERE `review_id` = '" . (int)$review_id . "' 
			ORDER BY `sort_order` ASC");
		
		return $query->rows;
	}

	/**
	 * Get Reviews By Product ID
	 *
	 * Get the record of the reviews by product records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> review records that have product ID
	 */
	public function getReviewsByProductId(int $product_id, int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT `r`.`review_id`, `r`.`author`, `r`.`rating`, `r`.`text`, `r`.`date_added` 
			FROM `" . DB_PREFIX . "review` `r` 
			LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`r`.`product_id` = `p`.`product_id`) 
			LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) 
			WHERE `r`.`product_id` = '" . (int)$product_id . "' 
				AND `p`.`date_available` <= NOW() 
				AND `p`.`status` = '1' 
				AND `r`.`status` = '1' 
				AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' 
			ORDER BY `r`.`date_added` DESC 
			LIMIT " . (int)$start . "," . (int)$limit);

		$reviews = $query->rows;

		// Thêm media cho mỗi review
		foreach ($reviews as $key => $review) {
			$reviews[$key]['media'] = $this->getReviewMedia($review['review_id']);
		}

		return $reviews;
	}

	/**
	 * Get Total Reviews By Product ID
	 *
	 * Get the total number of total review records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return int total number of review records that have product ID
	 */
	public function getTotalReviewsByProductId(int $product_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` 
			FROM `" . DB_PREFIX . "review` `r` 
			LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`r`.`product_id` = `p`.`product_id`) 
			LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) 
			WHERE `p`.`product_id` = '" . (int)$product_id . "' 
				AND `p`.`date_available` <= NOW() 
				AND `p`.`status` = '1' 
				AND `r`.`status` = '1' 
				AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return (int)$query->row['total'];
	}
}