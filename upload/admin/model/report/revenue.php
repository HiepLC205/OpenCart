<?php
namespace Opencart\Admin\Model\Report;
use Opencart\System\Engine\Model;

class Revenue extends Model {
    public function getRevenueByDay($start, $end, $status_id = 5) {
        $start = $this->db->escape($start);
        $end = $this->db->escape($end);

        $sql = "SELECT DATE(date_added) AS day, SUM(total) AS revenue FROM `" . DB_PREFIX . "order` o ";
        $sql .= "WHERE DATE(o.date_added) BETWEEN '" . $start . "' AND '" . $end . "' ";
        if ($status_id) {
            $sql .= "AND o.order_status_id = " . (int)$status_id . " ";
        } else {
            $sql .= "AND o.order_status_id > 0 ";
        }
        $sql .= "GROUP BY DATE(o.date_added) ORDER BY DATE(o.date_added) ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getRevenueByMonth($start, $end, $status_id = 5) {
        $start = $this->db->escape($start);
        $end = $this->db->escape($end);

        $sql = "SELECT DATE_FORMAT(date_added, '%Y-%m') AS month, SUM(total) AS revenue FROM `" . DB_PREFIX . "order` o ";
        $sql .= "WHERE DATE(o.date_added) BETWEEN '" . $start . "' AND '" . $end . "' ";
        if ($status_id) {
            $sql .= "AND o.order_status_id = " . (int)$status_id . " ";
        } else {
            $sql .= "AND o.order_status_id > 0 ";
        }
        $sql .= "GROUP BY DATE_FORMAT(o.date_added, '%Y-%m') ORDER BY month ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTopProducts($start, $end, $status_id = 5, $limit = 10) {
        $start = $this->db->escape($start);
        $end = $this->db->escape($end);
        $limit = (int)$limit;

        $sql = "SELECT op.product_id, pd.name, SUM(op.quantity) AS quantity, SUM(op.total) AS revenue ";
        $sql .= "FROM `" . DB_PREFIX . "order_product` op ";
        $sql .= "JOIN `" . DB_PREFIX . "order` o ON op.order_id = o.order_id ";
        $sql .= "LEFT JOIN `" . DB_PREFIX . "product_description` pd ON op.product_id = pd.product_id ";
        $sql .= "WHERE DATE(o.date_added) BETWEEN '" . $start . "' AND '" . $end . "' ";
        
        // SỬA LỖI LOGIC: Thêm điều kiện lọc ngôn ngữ cho tên sản phẩm
        $sql .= "AND pd.language_id = " . (int)$this->config->get('config_language_id') . " ";
        
        if ($status_id) {
            $sql .= "AND o.order_status_id = " . (int)$status_id . " ";
        } else {
            $sql .= "AND o.order_status_id > 0 ";
        }
        $sql .= "GROUP BY op.product_id ORDER BY quantity DESC LIMIT " . $limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTopCustomers($start, $end, $status_id = 5, $limit = 10) {
        $start = $this->db->escape($start);
        $end = $this->db->escape($end);
        $limit = (int)$limit;

        $sql = "SELECT o.customer_id, CONCAT(o.firstname, ' ', o.lastname) AS name, COUNT(o.order_id) AS orders, SUM(o.total) AS total ";
        $sql .= "FROM `" . DB_PREFIX . "order` o ";
        $sql .= "WHERE DATE(o.date_added) BETWEEN '" . $start . "' AND '" . $end . "' ";
        if ($status_id) {
            $sql .= "AND o.order_status_id = " . (int)$status_id . " ";
        } else {
            $sql .= "AND o.order_status_id > 0 ";
        }
        $sql .= "GROUP BY o.customer_id ORDER BY total DESC LIMIT " . $limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }
    public function getDailyRevenue($start, $end) {
        $start = $this->db->escape($start);
        $end = $this->db->escape($end);

        $query = $this->db->query("
            SELECT date, orders, revenue
            FROM revenue_daily
            WHERE date BETWEEN '" . $start . "' AND '" . $end . "'
            ORDER BY date ASC
        ");

       return $query->rows;
    }
}