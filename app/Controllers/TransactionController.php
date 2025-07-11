<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TransactionController extends ResourceController
{
    protected $modelName = TransactionModel::class;
    // protected $format    = 'html'; // We'll return views or redirects, so format might not be strictly needed here.

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface|string
     */
    public function index()
    {
        // To be implemented in a later step (Riwayat Transaksi)
        // For now, let's just load a simple view or message.
        $transactionModel = new TransactionModel();
        $data = [
            'transactions' => $transactionModel
                                ->select('transactions.*, users.name as cashier_name')
                                ->join('users', 'users.id = transactions.user_id', 'left')
                                ->orderBy('transactions.created_at', 'DESC')
                                ->paginate(10),
            'pager'        => $transactionModel->pager,
            'message'      => session()->getFlashdata('message'),
            'error'        => session()->getFlashdata('error'),
        ];
        return view('transactions/index', $data);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string
     */
    public function show($id = null)
    {
        $transactionModel = new TransactionModel();
        $transactionDetailModel = new TransactionDetailModel();

        $transaction = $transactionModel->find($id);

        if (!$transaction) {
            return redirect()->to('/transactions')->with('error', 'Transaction not found.');
        }

        $details = $transactionDetailModel
            ->select('transaction_details.*, products.name as product_name, products.code as product_code')
            ->join('products', 'products.id = transaction_details.product_id')
            ->where('transaction_details.transaction_id', $id)
            ->findAll();

        $data = [
            'transaction' => $transaction,
            'details'     => $details,
            'message'     => session()->getFlashdata('message'),
        ];

        return view('transactions/show', $data);
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface|string
     */
    public function new()
    {
        $productModel = new ProductModel();
        $data = [
            // Fetch only products that are not services or have stock > 0 if stock management is relevant
            // For now, fetching all products. Refine if product types/stock rules become complex.
            'products' => $productModel->orderBy('name', 'ASC')->findAll(), // Removed where('deleted_at IS NULL')
            'validation' => service('validation'),
            'error'    => session()->getFlashdata('error'), // To display errors from create if validation fails
        ];
        return view('transactions/new', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface|string
     */
    public function create()
    {
        $transactionModel = new TransactionModel();
        $transactionDetailModel = new TransactionDetailModel();
        $productModel = new ProductModel();

        $rules = [
            'customer_name' => 'permit_empty|string|max_length[255]',
            'payment_method' => 'permit_empty|string|max_length[50]',
            'discount' => 'permit_empty|decimal|greater_than_equal_to[0]', // Will use numeric in model, but decimal here is fine for format
            'products' => 'required', // Temporarily removed min_length[1]
            'products.*.id' => 'required|integer|is_not_unique[products.id]',
            'products.*.quantity' => 'required|integer|greater_than[0]',
        ];

        // $messages for min_length can be removed or kept if rule is restored
        $messages = [
            'products.required' => 'At least one product must be added to the transaction.',
            // 'products.min_length' => 'At least one product must be added to the transaction.',
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $transactionData = [
                'user_id' => session()->get('user_id'), // Assuming user_id is stored in session
                'customer_name' => $this->request->getPost('customer_name'),
                'payment_method' => $this->request->getPost('payment_method'),
                'discount' => $this->request->getPost('discount') ?: 0.00,
            ];

            $totalAmount = 0;
            $postedProducts = $this->request->getPost('products');
            $transactionDetailsData = []; // Renamed to avoid conflict with $details variable name

            foreach ($postedProducts as $p) {
                if (empty($p['id']) || empty($p['quantity'])) continue; // Skip if product ID or quantity is missing

                $product = $productModel->find($p['id']);
                if (!$product) {
                    throw new \Exception("Product with ID {$p['id']} not found.");
                }

                $subtotal = $product->price * $p['quantity'];
                $totalAmount += $subtotal;

                $transactionDetailsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $p['quantity'],
                    'price_per_unit' => $product->price,
                    'subtotal' => $subtotal,
                ];

                // Stock reduction as per AGENTS.md
                // "Pengurangan stok produk ATK secara otomatis (jika produk memiliki flag 'is_stock_managed')."
                // For now, we assume all products might have stock. If 'category' can identify ATK or a specific flag like 'is_stock_managed' exists, use that.
                // Let's assume products that are not services (e.g. category_id for ATK is known, or has 'unit' like 'pcs')
                // For simplicity, if 'stock' field is not null, we manage it.
                if ($product->stock !== null) {
                    $newStock = $product->stock - $p['quantity'];
                    if ($newStock < 0) {
                         throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$p['quantity']}");
                    }
                    if (!$productModel->update($product->id, ['stock' => $newStock])) {
                        throw new \Exception("Failed to update stock for product: {$product->name}. " . implode(', ', $productModel->errors()));
                    }
                }
            }

            if (empty($transactionDetailsData)) {
                throw new \Exception("No valid products were added to the transaction.");
            }

            $transactionData['total_amount'] = $totalAmount;
            $transactionData['final_amount'] = $totalAmount - $transactionData['discount'];

            if ($transactionData['final_amount'] < 0) {
                throw new \Exception("Final amount cannot be negative after discount. Total: {$totalAmount}, Discount: {$transactionData['discount']}");
            }

            // The generateTransactionCode callback in TransactionModel will set the transaction_code
            $transactionId = $transactionModel->insert($transactionData);

            if ($transactionId === false) {
                log_message('error', '[TransactionController::create] TransactionModel errors: ' . implode(', ', $transactionModel->errors()));
                log_message('error', '[TransactionController::create] TransactionData: ' . json_encode($transactionData));
                throw new \Exception('Failed to save transaction main data.');
            }

            foreach ($transactionDetailsData as &$detailItem) { // Renamed to avoid conflict
                $detailItem['transaction_id'] = $transactionId;
            }
            unset($detailItem);

            if (!$transactionDetailModel->insertBatch($transactionDetailsData)) {
                 throw new \Exception('Failed to save transaction details: ' . implode(', ', $transactionDetailModel->errors()));
            }

            $db->transCommit();
            return redirect()->to('transactions/' . $transactionId) // Redirect to transaction detail page
                             ->with('message', 'Transaction created successfully! Code: ' . $transactionModel->find($transactionId)->transaction_code);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[TransactionController::create] Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Error creating transaction: ' . $e->getMessage());
        }
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string
     */
    public function edit($id = null)
    {
        // Transactions are generally not editable in this simple system
        // If editing were allowed, it would typically be for pending/draft transactions or specific fields by an admin.
        // For now, redirect or show an error.
        return redirect()->to('transactions/' . $id)->with('error', 'Transactions are not editable.');
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string
     */
    public function update($id = null)
    {
        // Transactions are generally not editable.
        return redirect()->to('transactions/' . $id)->with('error', 'Transactions cannot be updated.');
    }

    /**
     * Delete the designated resource object from the model.
     * This would typically be a soft delete.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string
     */
    public function delete($id = null)
    {
        $transactionModel = new TransactionModel();
        $transaction = $transactionModel->find($id);

        if (!$transaction) {
            return redirect()->to('/transactions')->with('error', 'Transaction not found.');
        }

        // Implement logic to revert stock if a transaction is "deleted" (cancelled)
        // This requires careful consideration of states (e.g., can only cancel recent/pending transactions)
        // For now, a simple soft delete:
        if ($transactionModel->delete($id)) { // This will be a soft delete due to $useSoftDeletes = true in Model
            return redirect()->to('/transactions')->with('message', 'Transaction soft deleted successfully.');
        }

        $errors = $transactionModel->errors() ? implode(', ', $transactionModel->errors()) : 'Unknown error.';
        return redirect()->to('/transactions')->with('error', 'Failed to delete transaction: ' . $errors);
    }
}
