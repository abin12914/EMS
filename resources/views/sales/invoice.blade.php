@extends('layouts.app')
@section('title', 'Sale Receipt')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Sales<small>Receipt</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('sale.index') }}"> Sale</a></li>
            <li class="active"> Receipt</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="invoice">
        <div class="row">
            <div class="col-md-12">
                @include('sections.print-head')
                <h6 class="text-center">Receipt of sale</h6>
                <table class="table table-bordered" style="margin-bottom: 0px;">
                    <tbody>
                        <tr>
                            <td>
                                <table class="table table-bordered" style="margin-bottom: 0px;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 20%;">
                                                <b>Date & Ref. Number</b>
                                            </td>
                                            <td style="width: 30%;">
                                                {{ $sale->date->format('d-m-Y') }}&emsp;#{{ $sale->id }}/{{ $sale->transaction_id }}
                                            </td>
                                            <td style="width: 20%;">
                                                <b>Customer</b>
                                            </td>
                                            <td style="width: 30%;">
                                                {{ $sale->transaction->debitAccount->account_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 20%;">
                                                <b>Customer Detail</b>
                                            </td>
                                            <td style="width: 30%;">
                                                {{ $sale->supplier_name }}, {{ $sale->supplier_phone }}
                                            </td>
                                            <td style="width: 20%;">
                                                <b>Notes</b>
                                            </td>
                                            <td style="width: 30%;">
                                                {{ $sale->description }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h4>Sale Details</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 25%;">Product</th>
                                            <th style="width: 8%;">Gross Quantity</th>
                                            <th style="width: 8%;">Product Number</th>
                                            <th style="width: 8%;">Unit Wastage</th>
                                            <th style="width: 8%;">Total Wastage</th>
                                            <th style="width: 13%;">Net Quantity</th>
                                            <th style="width: 10%;">Rate</th>
                                            <th style="width: 15%;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->products as $index => $product)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>
                                                    {{ $product->saleDetail->gross_quantity ?: $product->saleDetail->net_quantity }}
                                                    {{ $product->uom_code }}
                                                </td>
                                                <td>{{ $product->saleDetail->product_number ?: '-' }}</td>
                                                <td>{{ $product->saleDetail->unit_wastage ?: '-' }}</td>
                                                <td>{{ $product->saleDetail->total_wastage ?: 0 }}</td>
                                                <td>{{ $product->saleDetail->net_quantity }} {{ $product->uom_code }}</td>
                                                <td>{{ $product->saleDetail->rate }}/-</td>
                                                <td>{{ ($product->saleDetail->net_quantity * $product->saleDetail->rate) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><strong>Total Price</strong></td>
                                            <td></td>
                                            <td>{{ ($sale->total_amount + $sale->discount) }}</td>
                                        </tr>
                                        @if(!empty($sale->discount) && $sale->discount > 0)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><strong>Discount</strong></td>
                                                <td></td>
                                                <td>{{ $sale->discount ?? 0}}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><strong>Total Bill</strong></td>
                                                <td></td>
                                                <td>{{ $sale->total_amount }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <h4>Payment Details</h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td style="width: 5%;">#</td>
                                            <td style="width: 65%;">Total Bill</td>
                                            <td style="width: 15%;">-</td>
                                            <td style="width: 15%;">{{ $sale->total_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td>#</td>
                                            @if($oldBalance == 0)
                                                <td>Old Balance</td>
                                                <td>-</td>
                                                <td>-</td>
                                            @elseif($oldBalance < 0)
                                                <td>
                                                    Old Balance 
                                                    <b> (Payable To {{ $sale->transaction->debitAccount->account_name }})</b>
                                                </td>
                                                <td>{{ abs($oldBalance) }}</td>
                                                <td>-</td>
                                            @else
                                                <td>
                                                    Old Balance 
                                                    <b> (Recievable From {{ $sale->transaction->debitAccount->account_name }})</b>
                                                </td>
                                                <td></td>
                                                <td>{{ abs($oldBalance) }}</td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <td>#</td>
                                            <td>Cash Received From Customer</td>
                                            <td>{{ (!empty($sale->payment) && $sale->payment->amount > 0) ? $sale->payment->amount : '-'  }}</td>
                                            <td>-</td>
                                        </tr>
                                    </tbody>
                                </table>
                                @if($oldBalance + $sale->total_amount - (!empty($sale->payment) ? $sale->payment->amount : 0) == 0)
                                    <h4>Outstanding Balance = 0/-
                                    </h4>
                                @elseif($oldBalance + $sale->total_amount - (!empty($sale->payment) ? $sale->payment->amount : 0) > 0)
                                    <h4>Outstanding Balance (Recievable From {{ $sale->transaction->debitAccount->account_name }}) = {{ abs($oldBalance + $sale->total_amount) - (!empty($sale->payment) ? $sale->payment->amount : 0) }}/-
                                    </h4>
                                @else
                                    <h4>Outstanding Balance (Payable To {{ $sale->transaction->debitAccount->account_name }}) = {{ abs($oldBalance + $sale->total_amount) - (!empty($sale->payment) ? $sale->payment->amount : 0) }}/-
                                    </h4>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row no-print">
            <div class="col-md-12">
                <a>
                    <button type="button" class="btn btn-lg btn-default" onclick="event.preventDefault(); print();">
                        <i class="fa fa-print"></i> Print Receipt
                    </button>
                </a>
                <a href="{{ route('sale.create') }}">
                    <button type="button" class="btn btn-lg btn-default">
                        <i class="fa fa-print"></i> New Sale
                    </button>
                </a>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(function () {
            window.print();
        });
    </script>
@endsection