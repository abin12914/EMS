@extends('layouts.app')
@section('title', 'Purchase Update')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Update
            <small>Purchase</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('purchase.index') }}"> Purchase</a></li>
            <li class="active"> Update</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row no-print">
            <div class="col-md-12">
                {{-- <div class="col-md-2"></div> --}}
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title" style="float: left;">Purchase Details</h3>
                                <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                        </div><br>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form action="{{route('purchase.store')}}" method="post" class="form-horizontal" autocomplete="off">
                            <div class="box-body">
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="purchase_date" class="control-label"><b style="color: red;">* </b> Purchase Date : </label>
                                                            <input type="text" class="form-control decimal_number_only datepicker" name="purchase_date" id="purchase_date" placeholder="Purchase date" value="{{ old('purchase_date', $purchase->date->format('d-m-Y')) }}" tabindex="1">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'purchase_date'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6" id="supplier_parent_div">
                                                            <label for="supplier_account_id" class="control-label"><b style="color: red;">* </b> Purchase From : </label>
                                                            {{-- adding account select component --}}
                                                            @component('components.selects.accounts', ['selectedAccountId' => old('supplier_account_id', $purchase->transaction->credit_account_id), 'cashAccountFlag' => true, 'selectName' => 'supplier_account_id', 'activeFlag' => false, 'nonAccountFlag' => true, 'tabindex' => 2])
                                                            @endcomponent
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'supplier_account_id'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="supplier_name" class="control-label"><b style="color: red;">* </b> Supplier Name : </label>
                                                            <input type="text" class="form-control" name="supplier_name" id="supplier_name" placeholder="Supplier name" value="{{ old('supplier_name', $purchase->supplier_name) }}" tabindex="3">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'supplier_name'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="supplier_phone" class="control-label"><b style="color: red;">* </b> Supplier Phone : </label>
                                                            <input type="text" class="form-control" name="supplier_phone" id="supplier_phone" placeholder="Supplier phone" value="{{ old('supplier_phone', $purchase->supplier_phone) }}" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'supplier_phone'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="description" class="control-label"><b style="color: red;">* </b> Notes : </label>
                                                    @if(empty(old('description')))
                                                        <textarea class="form-control" name="description" id="description" tabindex="5" rows="4" style="resize: none;" placeholder="description">{{ $purchase->description }}</textarea>
                                                    @else
                                                        <textarea class="form-control" name="description" id="description" tabindex="5" rows="4" style="resize: none;" placeholder="description">{{ old('description') }}</textarea>
                                                    @endif
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'description'])
                                                    @endcomponent
                                                </div>
                                            </div><br>
                                        </div>
                                        <br><br>
                                        <div class="form-group">
                                            <div class="row">
                                                <table class="table table-bordered table-hover dataTable">
                                                    <thead>
                                                        <th style="width: 5%;">#</th>
                                                        <th style="width: 35%;">Product</th>
                                                        <th style="width: 20%;">Weighment Note</th>
                                                        <th style="width: 15%;">Net Quantity</th>
                                                        <th style="width: 10%;">Rate</th>
                                                        <th style="width: 15%;">Amount</th>
                                                    </thead>
                                                    <tbody>
                                                        {{-- @for($i = 0; $i < 50; $i++) --}}
                                                        @foreach($purchase->products as $i => $product)
                                                            <tr id="product__row_{{ $i }}">
                                                                <td>
                                                                    @if(!empty($errors->first('product_id.'. $i)) || !empty($errors->first('net_quantity.'. $i)) || !empty($errors->first('purchase_rate.'. $i)) || !empty($errors->first('sub_bill.'. $i)))
                                                                        {{ $i + 1 }} &nbsp;
                                                                        <i class="fa fa-hand-o-right" style="color: red;" title="Invalid data in this row."></i>
                                                                    @else
                                                                        {{ $i + 1 }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @component('components.selects.products_custom', ['selectedProductId' => old('product_id.'. $i, $product->id), 'selectName' => 'product_id[]', 'selectId' => 'product_id_'.$i, 'customClassName' => 'products_combo', 'indexNo' => $i, 'tabindex' => (6 + $i), 'disabledOption' => true])
                                                                    @endcomponent
                                                                </td>
                                                                <td>
                                                                    <div class="col-md-12">
                                                                        <input type="text" class="form-control notes" name="notes[]" id="notes_{{ $i }}" readonly value="{{ !empty($product->purchaseDetail->gross_quantity) ? ($product->purchaseDetail->gross_quantity. ' - ('. $product->purchaseDetail->product_number.  ' nos x '. $product->purchaseDetail->unit_wastage. ') = '. $product->purchaseDetail->net_quantity) : '-' }}">
                                                                    </div>
                                                                    <input type="hidden" class="form-control gross_quantity" name="gross_quantity[]" id="gross_quantity_{{ $i }}" value="{{ old('gross_quantity.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                    <input type="hidden" class="form-control product_number" name="product_number[]" id="product_number_{{ $i }}" value="{{ old('product_number.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                    <input type="hidden" class="form-control unit_wastage " name="unit_wastage[]" id="unit_wastage_{{ $i }}" value="{{ old('unit_wastage.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                    <input type="hidden" class="form-control total_wastage" name="total_wastage[]" id="total_wastage_{{ $i }}" value="{{ old('total_wastage.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control decimal_number_only net_quantity" name="net_quantity[]" id="net_quantity_{{ $i }}" placeholder="Net Quantity" value="{{ old('net_quantity.'. $i, $product->purchaseDetail->net_quantity) }}" maxlength="4" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} tabindex="{{ 6 + $i }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control decimal_number_only purchase_rate" name="purchase_rate[]" id="purchase_rate_{{ $i }}" placeholder="Purchase rate" value="{{ old('purchase_rate.'. $i, $product->purchaseDetail->rate) }}" maxlength="6" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} tabindex="{{ 6 + $i }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control decimal_number_only sub_bill" name="sub_bill[]" id="sub_bill_{{ $i }}" placeholder="Bill value" value="{{ old('sub_bill.'.$i, ($product->purchaseDetail->net_quantity * $product->purchaseDetail->rate)) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Total</td>
                                                            <td>
                                                                @if(!empty($errors->first('total_amount')))
                                                                    <i class="fa fa-hand-o-right" style="color: red;" title="Something went wrong. Please try again."></i>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control decimal_number_only" name="total_amount" id="total_amount" placeholder="Total Amount" value="{{ old('total_amount', ($purchase->total_amount + $purchase->discount)) }}" readonly>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Discount</td>
                                                            <td>
                                                                @if(!empty($errors->first('discount')))
                                                                    &nbsp;<i class="fa fa-hand-o-right" style="color: red;" title="{{ $errors->first('discount') }}"></i>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control decimal_number_only" name="discount" id="discount" placeholder="Discount" value="{{ !empty(old('discount')) ? old('discount') : $purchase->discount }}" maxlength="6" tabindex="57">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Total Bill Amount</td>
                                                            <td>
                                                                @if(!empty($errors->first('total_bill')))
                                                                    <i class="fa fa-hand-o-right" style="color: red;" title="Something went wrong. Please try again."></i>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control decimal_number_only" name="total_bill" id="total_bill" placeholder="Total Bill Amount" value="{{ old('total_bill', $purchase->total_amount) }}" readonly>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div><br>
                                <div class="clearfix"> </div><br>
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-2">
                                        <button type="reset" class="btn btn-default btn-block btn-flat" tabindex="60">Clear</button>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" id="purchase_submit_button" class="btn btn-primary btn-block btn-flat" tabindex="59">Submit</button>
                                    </div>
                                    <!-- /.col -->
                                </div><br>
                                <div class="row text-center">
                                    {{-- adding error_message p tag component --}}
                                    @component('components.paragraph.error_message', ['fieldName' => 'calculations'])
                                    @endcomponent
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- /.box primary -->
                </div>
            </div>
        </div>
        <!-- /.row (main row) -->
    </section>
    <!-- /.content -->
</div>
<div class="modal modal-default" data-backdrop="static" data-keyboard="false" id="weighment_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal_close_button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-question-circle"> Weighment Details</i>
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-5 control-label">Product : <p class="pull-right">:</p></label>
                    <div class="col-sm-7">
                        <input type="text" id="modal_product" name="modal_product" class="form-control" style="width: 100%;" disabled>
                        <input type="hidden" name="modal_row_id" id="modal_row_id">
                    </div>
                </div><br><br>
                <div class="form-group">
                    <label class="col-sm-5 control-label">Gross Quantity : <p class="pull-right">:</p></label>
                    <div class="col-sm-7">
                        <input type="text" id="modal_gross_quatity" name="modal_gross_quatity" class="form-control decimal_number_only" style="width: 100%;">
                    </div>
                </div><br><br>
                <div class="form-group">
                    <label class="col-sm-5 control-label">Numbers : <p class="pull-right">:</p></label>
                    <div class="col-sm-7">
                        <input type="text" id="modal_numbers" name="modal_numbers" class="form-control decimal_number_only" style="width: 100%;">
                    </div>
                </div><br><br>
                <div class="form-group">
                    <label class="col-sm-5 control-label">Unit Wastage : <p class="pull-right">:</p></label>
                    <div class="col-sm-7">
                        <input type="text" id="modal_unit_wastage" name="modal_unit_wastage" class="form-control decimal_number_only" style="width: 100%;">
                    </div>
                </div><br><br>
                <div class="form-group">
                    <label class="col-sm-5 control-label">Total Wastage : <p class="pull-right">:</p></label>
                    <div class="col-sm-7">
                        <input type="text" id="modal_total_wastage" name="modal_total_wastage" class="form-control" style="width: 100%;" readonly>
                    </div>
                </div><br><br>
                <div class="form-group">
                    <label class="col-sm-5 control-label">Net Quantity : <p class="pull-right">:</p></label>
                    <div class="col-sm-7">
                        <input type="text" id="modal_net_quantity" name="modal_net_quantity" class="form-control" style="width: 100%;" readonly>
                    </div>
                </div><br><br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning pull-left modal_close_button" data-dismiss="modal">Cancel & Proceed W/o Quantity Deduction</button>
                <button type="button" id="btn_modal_weighment_submit" class="btn btn-info">Confirm & Add Deduction Details</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection
@section('scripts')
    <script src="/js/registrations/purchaseRegistration.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection