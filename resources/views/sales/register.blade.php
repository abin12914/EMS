@extends('layouts.app')
@section('title', 'Sale Registration')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Register
            <small>Sale</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('sale.index') }}"> Sale</a></li>
            <li class="active"> Registration</li>
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
                            <h3 class="box-title" style="float: left;">Sale Details</h3>
                                <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                        </div><br>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form action="{{route('sale.store')}}" method="post" class="form-horizontal" autocomplete="off">
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
                                                            <label for="sale_date" class="control-label"><b style="color: red;">* </b> Sale Date : </label>
                                                            <input type="text" class="form-control decimal_number_only datepicker_reg" name="sale_date" id="sale_date" placeholder="Sale date" value="{{ old('sale_date') }}" tabindex="1">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'sale_date'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6" id="customer_parent_div">
                                                            <label for="customer_account_id" class="control-label"><b style="color: red;">* </b> Sale To : </label>
                                                            {{-- adding account select component --}}
                                                            @component('components.selects.accounts', ['selectedAccountId' => old('customer_account_id'), 'cashAccountFlag' => true, 'selectName' => 'customer_account_id', 'activeFlag' => false, 'nonAccountFlag' => true, 'tabindex' => 2])
                                                            @endcomponent
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'customer_account_id'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="customer_name" class="control-label"><b style="color: red;">* </b> Customer Name : </label>
                                                            <input type="text" class="form-control" name="customer_name" id="customer_name" placeholder="Customer name" value="{{ old('customer_name') }}" tabindex="3">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'customer_name'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="customer_phone" class="control-label"><b style="color: red;">* </b> Customer Phone : </label>
                                                            <input type="text" class="form-control" name="customer_phone" id="customer_phone" placeholder="Customer phone" value="{{ old('customer_phone') }}" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'customer_phone'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="description" class="control-label"><b style="color: red;">* </b> Notes : </label>
                                                    @if(empty(old('description')))
                                                        <textarea class="form-control" name="description" id="description" tabindex="5" rows="4" style="resize: none;" placeholder="description"></textarea>
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
                                                        <th style="width: 20%;">Notes</th>
                                                        <th style="width: 15%;">Net Quantity</th>
                                                        <th style="width: 10%;">Rate</th>
                                                        <th style="width: 15%;">Amount</th>
                                                    </thead>
                                                    <tbody>
                                                        @for($i = 0; $i < 50; $i++)
                                                            <tr id="product__row_{{ $i }}" style="display : {{ (($i > 2) && empty(old('product_id.'. $i ))) ? 'none' : '' }}">
                                                                <td>
                                                                    @if(!empty($errors->first('product_id.'. $i)) || !empty($errors->first('net_quantity.'. $i)) || !empty($errors->first('sale_rate.'. $i)) || !empty($errors->first('sub_bill.'. $i)))
                                                                        {{ $i + 1 }} &nbsp;
                                                                        <i class="fa fa-hand-o-right" style="color: red;" title="Invalid data in this row."></i>
                                                                    @else
                                                                        {{ $i + 1 }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @component('components.selects.products_custom', ['selectedProductId' => old('product_id.'. $i), 'selectName' => 'product_id[]', 'selectId' => 'product_id_'.$i, 'customClassName' => 'products_combo', 'indexNo' => $i, 'tabindex' => (6 + $i), 'disabledOption' => (empty(old('product_id.'. ($i-1))) && $i > 0 ? true : false )])
                                                                    @endcomponent
                                                                </td>
                                                                <td>
                                                                    <div class="col-md-9">
                                                                        <input type="text" class="form-control notes" name="notes[]" id="notes_{{ $i }}" value="{{ old('product_id.'. $i) }}" readonly>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <button type="button" id="add_note_{{ $i }}" class="btn btn-primary btn-block btn-flat add_note">
                                                                            <i class="fa fa-plus"></i>
                                                                        </button>
                                                                    </div>
                                                                    <input type="hidden" class="form-control gross_quantity" name="gross_quantity[]" id="gross_quantity_{{ $i }}" value="{{ old('gross_quantity.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                    <input type="hidden" class="form-control product_number" name="product_number[]" id="product_number_{{ $i }}" value="{{ old('product_number.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                    <input type="hidden" class="form-control unit_wastage " name="unit_wastage[]" id="unit_wastage_{{ $i }}" value="{{ old('unit_wastage.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                    <input type="hidden" class="form-control total_wastage" name="total_wastage[]" id="total_wastage_{{ $i }}" value="{{ old('total_wastage.'. $i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control decimal_number_only net_quantity" name="net_quantity[]" id="net_quantity_{{ $i }}" placeholder="Net Quantity" value="{{ old('net_quantity.'. $i) }}" maxlength="4" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} tabindex="{{ 6 + $i }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control decimal_number_only sale_rate" name="sale_rate[]" id="sale_rate_{{ $i }}" placeholder="Sale rate" value="{{ old('sale_rate.'. $i) }}" maxlength="6" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} tabindex="{{ 6 + $i }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control decimal_number_only sub_bill" name="sub_bill[]" id="sub_bill_{{ $i }}" placeholder="Bill value" value="{{ old('sub_bill.'.$i) }}" {{ empty(old('product_id.'. $i )) ? 'disabled' : '' }} readonly>
                                                                </td>
                                                            </tr>
                                                        @endfor
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
                                                                <input type="text" class="form-control decimal_number_only" name="total_amount" id="total_amount" placeholder="Total Amount" value="{{ old('total_amount') }}" readonly>
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
                                                                <input type="text" class="form-control decimal_number_only" name="discount" id="discount" placeholder="Discount" value="{{ !empty(old('discount')) ? old('discount') : 0 }}" maxlength="6" tabindex="57">
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
                                                                <input type="text" class="form-control decimal_number_only" name="total_bill" id="total_bill" placeholder="Total Bill Amount" value="{{ old('total_bill') }}" readonly>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Old Balance <i id="ob_info"></i></td>
                                                            <td></td>
                                                            <td>
                                                                <input type="text" class="form-control" name="old_balance" id="old_balance" placeholder="Old Balance" value="{{ old('old_balance') ?: 0 }}" readonly>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Total</td>
                                                            <td></td>
                                                            <td>
                                                                <input type="text" class="form-control" name="bill_plus_ob_amount" id="bill_plus_ob_amount" placeholder="Bill + Old Balance" value="{{ old('bill_plus_ob_amount') }}" readonly>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Cash Received From Customer</td>
                                                            <td></td>
                                                            <td>
                                                                <input type="text" class="form-control decimal_number_only" name="cash_received" id="cash_received" placeholder="Cash Received" value="{{ old('cash_received') ?: 0 }}" maxlength="6" tabindex="58">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Outstanding Balance</td>
                                                            <td>
                                                                @if(!empty($errors->first('calculations')))
                                                                    <i class="fa fa-hand-o-right" style="color: red;" title="Error in calculations. Try again later."></i>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" name="outstanding_amount" id="outstanding_amount" placeholder="Outstanding Balance" value="{{ old('outstanding_amount') }}" readonly>
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
                                        <button type="button" id="sale_submit_button" class="btn btn-primary btn-block btn-flat" tabindex="59">Submit</button>
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
    <script src="/js/registrations/saleRegistration.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection