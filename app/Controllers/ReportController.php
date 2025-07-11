<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use CodeIgniter\RESTful\ResourceController; // Using ResourceController for consistency, can be BaseController
use DateTime; // For date manipulation

class ReportController extends ResourceController // Or BaseController
{
    // If not using full RESTful, you might not need $modelName or $format
    // protected $modelName = '';
    // protected $format    = 'html';

    public function dailySales()
    {
        $transactionModel = new TransactionModel();
        $request = service('request');

        // Get date from_date and to_date from GET request, default to today
        $fromDateStr = $request->getGet('from_date') ?? date('Y-m-d');
        $toDateStr = $request->getGet('to_date') ?? date('Y-m-d');

        // Validate date format (basic validation)
        $fromDate = DateTime::createFromFormat('Y-m-d', $fromDateStr);
        $toDate = DateTime::createFromFormat('Y-m-d', $toDateStr);

        if (!$fromDate || !$toDate || $fromDate->format('Y-m-d') !== $fromDateStr || $toDate->format('Y-m-d') !== $toDateStr) {
            return redirect()->to('reports/sales/daily')
                             ->with('error', 'Format tanggal tidak valid. Gunakan YYYY-MM-DD.');
        }

        if ($fromDate > $toDate) {
            return redirect()->to('reports/sales/daily')
                             ->withInput() // Keep the invalid dates in the form
                             ->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
        }

        // Adjust to_date to include the whole day
        $toDate->setTime(23, 59, 59);

        $reportData = $transactionModel
            ->select('DATE(created_at) as transaction_date, COUNT(id) as total_transactions, SUM(final_amount) as total_sales')
            ->where('created_at >=', $fromDate->format('Y-m-d H:i:s'))
            ->where('created_at <=', $toDate->format('Y-m-d H:i:s'))
            ->groupBy('DATE(created_at)')
            ->orderBy('transaction_date', 'ASC')
            ->findAll();

        $summary = [
            'total_transactions' => array_sum(array_column($reportData, 'total_transactions')),
            'total_sales' => array_sum(array_column($reportData, 'total_sales'))
        ];
        log_message('error', '[ReportController] Date Range: ' . $fromDate->format('Y-m-d') . ' to ' . $toDate->format('Y-m-d H:i:s'));
        log_message('error', '[ReportController] ReportData: ' . json_encode($reportData));
        log_message('error', '[ReportController] Summary Sales: ' . $summary['total_sales']);

        $data = [
            'title'      => 'Laporan Penjualan Harian',
            'reportData' => $reportData,
            'summary'    => $summary,
            // 'debug_total_sales' => $summary['total_sales'], // DEBUG REMOVED
            // 'debug_tx_count' => $summary['total_transactions'], // DEBUG REMOVED
            'fromDate'   => $fromDate->format('Y-m-d'),
            'toDate'     => $request->getGet('to_date') ?? date('Y-m-d'), // Use original toDate for form prefill
            'message'    => session()->getFlashdata('message'),
            'error'      => session()->getFlashdata('error'),
        ];

        return view('reports/daily_sales', $data);
    }

    public function topProducts()
    {
        $transactionDetailModel = new TransactionDetailModel();
        $request = service('request');

        $today = date('Y-m-d');
        $firstDayOfMonth = date('Y-m-01');

        $fromDateStr = $request->getGet('from_date') ?? $firstDayOfMonth;
        $toDateStr = $request->getGet('to_date') ?? $today;

        $limit = (int)($request->getGet('limit') ?? 10);
        if ($limit <= 0) $limit = 10;


        $fromDate = DateTime::createFromFormat('Y-m-d', $fromDateStr);
        $toDate = DateTime::createFromFormat('Y-m-d', $toDateStr);

        if (!$fromDate || !$toDate || $fromDate->format('Y-m-d') !== $fromDateStr || $toDate->format('Y-m-d') !== $toDateStr) {
             return redirect()->to('reports/sales/top-products')
                             ->with('error', 'Format tanggal tidak valid. Gunakan YYYY-MM-DD.');
        }
        if ($fromDate > $toDate) {
            return redirect()->to('reports/sales/top-products')
                             ->withInput()
                             ->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
        }

        $toDate->setTime(23, 59, 59); // Include whole day

        $topProducts = $transactionDetailModel
            ->select('products.name as product_name, products.code as product_code, SUM(transaction_details.quantity) as total_quantity_sold, SUM(transaction_details.subtotal) as total_revenue')
            ->join('products', 'products.id = transaction_details.product_id')
            ->join('transactions', 'transactions.id = transaction_details.transaction_id') // Join transactions to filter by date
            ->where('transactions.created_at >=', $fromDate->format('Y-m-d H:i:s'))
            ->where('transactions.created_at <=', $toDate->format('Y-m-d H:i:s'))
            ->groupBy('transaction_details.product_id, products.name, products.code')
            ->orderBy('total_quantity_sold', 'DESC')
            ->limit($limit)
            ->findAll();

        $data = [
            'title'       => "Top {$limit} Produk Terlaris",
            'topProducts' => $topProducts,
            'fromDate'    => $fromDate->format('Y-m-d'),
            'toDate'      => $request->getGet('to_date') ?? $today, // Use original toDate for form prefill
            'limit'       => $limit,
            'message'     => session()->getFlashdata('message'),
            'error'       => session()->getFlashdata('error'),
        ];

        return view('reports/top_products', $data);
    }
}
