<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Product;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceRequest;
use App\Http\Resources\Admin\InvoiceResource;


class InvoiceController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        // $this->authorize('manage_users');

        $this->authorize('showAll',Invoice::class);
        $Invoices = Invoice::orderBy('created_at', 'desc')->paginate(10);
        return response()->json([
            'data' => $Invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'customerName' => $invoice->customerName,
                    'invoiceAfterDiscount' =>$invoice->invoiceAfterDiscount,
                    'creationDate' => $invoice->creationDate,

                ];
            }),
              'pagination' => [
                        'total' => $Invoices->total(),
                        'count' => $Invoices->count(),
                        'per_page' => $Invoices->perPage(),
                        'current_page' => $Invoices->currentPage(),
                        'total_pages' => $Invoices->lastPage(),
                        'next_page_url' => $Invoices->nextPageUrl(),
                        'prev_page_url' => $Invoices->previousPageUrl(),
                    ],
            'message' => "Show All Invoices Successfully."
        ]);
    }


public function create(InvoiceRequest $request)
{
    // $this->authorize('manage_users');
    $this->authorize('create',Invoice::class);

    $Invoice = Invoice::create([
        "customerName" => $request->customerName,
        "sellerName" => $request->sellerName,
        "discount" => $request->discount,
        "extraAmount" => $request->extraAmount ?? 0,
        'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
    ]);

    $totalProfit = 0;
    $totalSellingPrice = 0;
    $extraAmount = $request->extraAmount ?? 0;

    if ($request->has('products')) {
        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);

            if ($productModel->quantity <= 0) {
                return response()->json([
                    'message' => "Product '{$productModel->name}' is out of stock and cannot be added to the invoice.",
                ], 400);
            }

            if ($product['quantity'] > $productModel->quantity) {
                return response()->json([
                    'message' => "Not enough quantity for product '{$productModel->name}'. Available: {$productModel->quantity}.",
                ], 400);
            }

            $productModel->decrement('quantity', $product['quantity']);

            if ($productModel->quantity === 0) {
                $outOfStockProducts[] = $productModel->name;
            }

            $totalSellingPriceForProduct = $productModel->sellingPrice * $product['quantity'];
            $totalSellingPrice += $totalSellingPriceForProduct;

            $profitForProduct = ($productModel->sellingPrice - $productModel->purchesPrice) * $product['quantity'];
            $totalProfit += $profitForProduct;

            $Invoice->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'total' => $totalSellingPriceForProduct,
                'profit' => $profitForProduct,
            ]);
        }
    }

    $discount = $Invoice->discount ?? 0;
    $totalSellingPrice += $extraAmount;
    $finalPrice = $totalSellingPrice - $discount;
    $netProfit = $totalProfit - $discount;

    $formattedTotalSellingPrice = number_format($totalSellingPrice, 2, '.', '');
    $formattedFinalPrice = number_format($finalPrice, 2, '.', '');
    $formattedNetProfit = number_format($netProfit, 2, '.', '');
    $formattedDiscount = number_format($discount, 2, '.', '');
    $formattedExtraAmount = number_format($extraAmount, 2, '.', '');

    $Invoice->update([
        'totalInvoicePrice' => $formattedTotalSellingPrice,
        'invoiceAfterDiscount' => $formattedFinalPrice,
        'profit' => $formattedNetProfit,
    ]);

    $Invoice->updateInvoiceProductCount();

    $warningMessage = null;
    if (!empty($outOfStockProducts)) {
        $warningMessage = "The following products are now out of stock: " . implode(', ', $outOfStockProducts);
    }

    return response()->json([
        'message' => 'Invoice created successfully',
        'invoice' => new InvoiceResource($Invoice->load('products')),
        'extraAmount' => $formattedExtraAmount,
        'totalInvoicePrice' => $formattedTotalSellingPrice,
        'discount' => $formattedDiscount,
        'invoiceAfterDiscount' => $formattedFinalPrice,
        'warning' => $warningMessage,
    ]);
}


    public function edit(string $id)
    {
        // $this->authorize('manage_users');

        $Invoice = Invoice::with('products')->find($id);

        if (!$Invoice) {
            return response()->json([
                'message' => "Invoice not found."
            ], 404);
        }

        $this->authorize('edit',$Invoice);

        $totalProfit = 0;
        $totalSellingPrice = 0;
        $extraAmount = $Invoice->extraAmount ?? 0;

        if ($Invoice->products->isNotEmpty()) {
            foreach ($Invoice->products as $product) {
                $totalSellingPriceForProduct = $product->pivot->total;
                $totalSellingPrice += $totalSellingPriceForProduct;

                $profitForProduct = ($product->sellingPrice - $product->purchesPrice) * $product->pivot->quantity;
                $totalProfit += $profitForProduct;
            }
        }

        $discount = $Invoice->discount ?? 0;
        $totalSellingPrice += $extraAmount;
        $finalPrice = $totalSellingPrice - $discount;
        $netProfit = $totalProfit - $discount;

        $formattedTotalSellingPrice = number_format($totalSellingPrice, 2, '.', '');
        $formattedFinalPrice = number_format($finalPrice, 2, '.', '');
        $formattedNetProfit = number_format($netProfit, 2, '.', '');
        $formattedDiscount = number_format($discount, 2, '.', '');
        $formattedExtraAmount = number_format($extraAmount, 2, '.', '');

        $Invoice->update([
            'totalInvoicePrice' => $formattedTotalSellingPrice,
            'invoiceAfterDiscount' => $formattedFinalPrice,
            'profit' => $formattedNetProfit,
        ]);

        return response()->json([
            'message' => 'Invoice details fetched successfully',
            'invoice' => new InvoiceResource($Invoice->load('products')),
            'extraAmount' => $formattedExtraAmount,
            'totalInvoicePrice' => $formattedTotalSellingPrice,
            'discount' => $formattedDiscount,
            'invoiceAfterDiscount' => $formattedFinalPrice,
            'warning' => null,
        ]);
    }






    public function update(InvoiceRequest $request, string $id)
{
    //  dd($request->all());
    // $this->authorize('manage_users');

    $Invoice = Invoice::findOrFail($id);

    if (!$Invoice) {
        return response()->json([
            'message' => "Invoice not found."
        ], 404);
    }
    $this->authorize('update',$Invoice);
    $previousProducts = $Invoice->products()
        ->select('products.id', 'invoice_products.quantity')
        ->pluck('invoice_products.quantity', 'products.id')
        ->toArray();

    $Invoice->update([
        "customerName" => $request->customerName,
        "sellerName" => $request->sellerName,
        "discount" => $request->discount,
        "extraAmount" => $request->extraAmount ?? 0,
        'creationDate' => now()->timezone('Africa/Cairo')->format('Y-m-d h:i:s'),
    ]);

    $totalProfit = 0;
    $totalSellingPrice = 0;
    $extraAmount = $request->extraAmount ?? 0;

    $outOfStockProducts = [];


    if ($request->has('products')) {
        $productsData = [];
        $errors = [];

        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);
            $previousQuantity = $previousProducts[$product['id']] ?? 0;
            $newQuantity = $product['quantity'];

            if ($newQuantity > $previousQuantity) {
                $difference = $newQuantity - $previousQuantity;

                if ($difference > $productModel->quantity) {
                    $errors[] = "Not enough quantity for product '{$productModel->name}'. Available: {$productModel->quantity}.";
                    continue;
                }


                $productModel->decrement('quantity', $difference);
            } elseif ($newQuantity < $previousQuantity) {
                $difference = $previousQuantity - $newQuantity;


                $productModel->increment('quantity', $difference);
            }


            if ($productModel->quantity <= 0) {
                $outOfStockProducts[] = $productModel->name;
            }


            $totalSellingPriceForProduct = $productModel->sellingPrice * $newQuantity;
            $totalSellingPrice += $totalSellingPriceForProduct;

            $profitForProduct = ($productModel->sellingPrice - $productModel->purchesPrice) * $newQuantity;
            $totalProfit += $profitForProduct;

            $productsData[$product['id']] = [
                'quantity' => $newQuantity,
                'total' => $totalSellingPriceForProduct,
                'profit' => $profitForProduct,
            ];
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Some errors occurred while processing the invoice.',
                'errors' => $errors,
            ], 400);
        }

        $warningMessage = null;
        if (!empty($outOfStockProducts)) {
            $warningMessage = "The following products are now out of stock: " . implode(', ', $outOfStockProducts);
        }

        $Invoice->products()->sync($productsData);
    }

    $discount = $Invoice->discount ?? 0;
    $totalSellingPrice += $extraAmount;
    $finalPrice = $totalSellingPrice - $discount;
    $netProfit = $totalProfit - $discount;

    $formattedTotalSellingPrice = number_format($totalSellingPrice, 2, '.', '');
    $formattedFinalPrice = number_format($finalPrice, 2, '.', '');
    $formattedNetProfit = number_format($netProfit, 2, '.', '');
    $formattedDiscount = number_format($discount, 2, '.', '');
    $formattedExtraAmount = number_format($extraAmount, 2, '.', '');

    $Invoice->update([
        'totalInvoicePrice' => $formattedTotalSellingPrice,
        'invoiceAfterDiscount' => $formattedFinalPrice,
        'profit' => $formattedNetProfit,
    ]);

    $Invoice->updateInvoiceProductCount();
    // dd($Invoice);
    return response()->json([
        'message' => 'Invoice updated successfully.',
        'invoice' => new InvoiceResource($Invoice->load('products')),
        'extraAmount' => $formattedExtraAmount,
        'totalInvoicePrice' => $formattedTotalSellingPrice,
        'discount' => $formattedDiscount,
        'invoiceAfterDiscount' => $formattedFinalPrice,
        'warning' => $warningMessage,
    ]);
}




  public function destroy(string $id)
  {
      return $this->destroyModel(Invoice::class, InvoiceResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Invoices=Invoice::onlyTrashed()->get();
return response()->json([
    'data' =>InvoiceResource::collection($Invoices),
    'message' => "Show Deleted Invoices Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Invoice = Invoice::withTrashed()->where('id', $id)->first();
if (!$Invoice) {
    return response()->json([
        'message' => "Invoice not found."
    ], 404);
}
$Invoice->restore();
return response()->json([
    'data' =>new InvoiceResource($Invoice),
    'message' => "Restore Invoice By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Invoice::class, $id);
  }



}
