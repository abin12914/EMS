@extends('layouts.app')
@section('title', 'Purchase Details')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Purchase
            <small>Details</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('purchase.index') }}"> Purchase</a></li>
            <li class="active"> Details</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row no-print">
            <div class="col-md-12">
                <div class="col-md-12">
                    <!-- Widget: user widget style 1 -->
                    <div class="box box-widget widget-user-2">
                        @if(!empty($purchase))
                            <!-- Add the bg color to the header using any of the bg-* classes -->
                            <div class="widget-user-header bg-yellow">
                                <div class="widget-user-image">
                                    <img class="img-circle" src="/images/public/cart.png" alt="User Avatar">
                                </div>
                                <!-- /.widget-user-image -->
                                <h3 class="widget-user-username">Purchase</h3>
                                <h5 class="widget-user-desc">Ref : #{{ $purchase->id }}/{{ $purchase->transaction->id }}</h5>
                            </div>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>
                                                <i class="fa fa-calendar margin-r-5"></i> Date
                                            </strong>
                                            <p class="text-muted multi-line">
                                                <strong>{{ $purchase->date->format('d-m-Y') }}</strong>
                                            </p>
                                            <hr>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>
                                                <i class="fa fa-user-o margin-r-5"></i> Supplier Account
                                            </strong>
                                            <p class="text-muted multi-line">
                                                {{ $purchase->transaction->creditAccount->account_name }}
                                            </p>
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>
                                                <i class="fa fa-phone margin-r-5"></i> Supplier Name & Phone
                                            </strong>
                                            <p class="text-muted multi-line">
                                                {{ $purchase->supplier_name }} - {{ $purchase->supplier_phone }}
                                            </p>
                                            <hr>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>
                                                <i class="fa fa-edit margin-r-5"></i> Notes
                                            </strong>
                                            <p class="text-muted multi-line">
                                                {{ $purchase->description }}
                                            </p>
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>
                                                <i class="fa fa-calculator margin-r-5"></i> Bill Amount
                                            </strong>
                                            <p class="text-muted multi-line">
                                                {{ $purchase->total_amount }}
                                            </p>
                                            <hr>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>
                                                <i class="fa fa-inr margin-r-5"></i> Cash Paid
                                            </strong>
                                            <p class="text-muted multi-line">
                                                {{ !empty($purchase->payment) ? $purchase->payment->amount : 0 }}
                                            </p>
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Purchase Items</h3>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-responsive table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;">#</th>
                                                        <th style="width: 30%;">Item</th>
                                                        <th style="width: 10%;">Gross Quantity</th>
                                                        <th style="width: 5%;">Product Number</th>
                                                        <th style="width: 10%;">Unit Wastage</th>
                                                        <th style="width: 10%;">Total Wastage</th>
                                                        <th style="width: 10%;">Net Quantity</th>
                                                        <th style="width: 10%;">Rate</th>
                                                        <th style="width: 10%;">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($purchase->products as $index => $product)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $product->name. " - ". $product->malayalam_name }}</td>
                                                            <td>
                                                                {{ $product->purchaseDetail->gross_quantity ?: $product->purchaseDetail->net_quantity }}
                                                            </td>
                                                            <td>{{ $product->purchaseDetail->product_number ?: '-' }}</td>
                                                            <td>{{ $product->purchaseDetail->unit_wastage ?: '-' }}</td>
                                                            <td>
                                                                {{ !empty($product->purchaseDetail->gross_quantity) ? $product->purchaseDetail->total_wastage : '0' }}
                                                            </td>
                                                            <td>{{ $product->purchaseDetail->net_quantity }}</td>
                                                            <td>{{ $product->purchaseDetail->rate }}</td>
                                                            <td>{{ $product->purchaseDetail->net_quantity * $product->purchaseDetail->rate }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><strong>Total</strong></td>
                                                        <td></td>
                                                        <td><strong>{{ $purchase->total_amount + $purchase->discount }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><strong>Discount</strong></td>
                                                        <td></td>
                                                        <td><strong>{{ $purchase->discount ?? 0 }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><strong>Total Bill Amount</strong></td>
                                                        <td></td>
                                                        <td><strong>{{ $purchase->total_amount }}</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div><br>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <div class="clearfix"> </div>
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6">
                                            <div class="col-md-4">
                                                <a href="{{ route('purchase.invoice', ['id' => $purchase->id]) }}" target="_blank">
                                                    <button type="button" class="btn btn-default btn-block btn-flat"><i class="fa fa-print"></i> Invoice</button>
                                                </a>
                                            </div>
                                            <div class="col-md-4">
                                                <form action="{{ route('purchase.edit', $purchase->id) }}" method="get" class="form-horizontal">
                                                    <button type="submit" class="btn btn-primary btn-block btn-flat">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="col-md-4">
                                                <form action="{{ route('purchase.destroy', $purchase->id) }}" method="post" class="form-horizontal">
                                                    {{ method_field('DELETE') }}
                                                    {{ csrf_field() }}
                                                    <button type="button" class="btn btn-danger btn-block btn-flat delete_button">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box -->
                        @endif
                    </div>
                    <!-- /.widget-user -->
                </div>
            </div>
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
@endsection