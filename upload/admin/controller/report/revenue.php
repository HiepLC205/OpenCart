<?php
namespace Opencart\Admin\Controller\Report;

class Revenue extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('report/revenue');
        $this->load->model('report/revenue');

        // Breadcrumbs
        $data['breadcrumbs'] = [];
        //Trang Dashboard
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        //Trang báo cáo doanh thu
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('report/revenue', 'user_token=' . $this->session->data['user_token'])
        ];

        // Filters mặc định (lọc dữ liệu)
        $data['filter_start'] = $this->request->get['filter_start'] ?? date('Y-m-d', strtotime('-7 days'));
        $data['filter_end']   = $this->request->get['filter_end'] ?? date('Y-m-d');
        $data['status_id']    = isset($this->request->get['status_id']) ? (int)$this->request->get['status_id'] : 5;

        // Fetch data từ model (Lấy dữ liệu từ model và top sản phẩm)
        $results_days   = $this->model_report_revenue->getRevenueByDay($data['filter_start'], $data['filter_end'], $data['status_id']);
        $results_months = $this->model_report_revenue->getRevenueByMonth($data['filter_start'], $data['filter_end'], $data['status_id']);
        $data['top_products']  = $this->model_report_revenue->getTopProducts($data['filter_start'], $data['filter_end'], $data['status_id']);
        $data['top_customers'] = $this->model_report_revenue->getTopCustomers($data['filter_start'], $data['filter_end'], $data['status_id']);

        // Chuẩn bị dữ liệu cho biểu đồ chart
        $data['chart_days']           = json_encode(array_column($results_days, 'day'));
        $data['chart_revenue_days']   = json_encode(array_map('floatval', array_column($results_days, 'revenue')));
        $data['chart_months']         = json_encode(array_column($results_months, 'month'));
        $data['chart_revenue_months'] = json_encode(array_map('floatval', array_column($results_months, 'revenue')));

        $data['user_token'] = $this->session->data['user_token'];

        // Thêm action URL cho form bộ lọc
        $data['action'] = $this->url->link('report/revenue', 'user_token=' . $this->session->data['user_token'], true);

        // Header, Column, Footer
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        // Render template
        $this->response->setOutput($this->load->view('report/revenue', $data));
    }
}
