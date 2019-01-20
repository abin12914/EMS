@extends('layouts.app')
@section('title', 'Excavator Rent Edit')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Register
            <small>Excavator Rent</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('excavator-rent.index') }}"> Excavator Rent</a></li>
            <li class="active"> Edit</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row no-print">
            <div class="col-md-12">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title" style="float: left;">Excavator Rent Details</h3>
                                <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                        </div><br>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form action="{{route('excavator-rent.update', $excavatorRent->id)}}" method="post" class="form-horizontal" autocomplete="off">
                            @csrf()
                            @method('PUT')
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="excavator_id" class="control-label"><b style="color: red;">* </b> Excavator : </label>
                                                    {{-- adding excavator select component --}}
                                                    @component('components.selects.excavators', ['selectedExcavatorId' => old('excavator_id', $excavatorRent->excavator_id), 'selectName' => 'excavator_id', 'tabindex' => 1])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'excavator_id'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="account_id" class="control-label"><b style="color: red;">* </b> Customer : </label>
                                                    {{-- adding account select component --}}
                                                    @component('components.selects.accounts', ['selectedAccountId' => old('account_id', $excavatorRent->transaction->debit_account_id), 'cashAccountFlag' => true, 'selectName' => 'account_id', 'activeFlag' => true, 'tabindex' => 2])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'account_id'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="site_id" class="control-label"><b style="color: red;">* </b> Site : </label>
                                                    {{-- adding excavator select component --}}
                                                    @component('components.selects.sites', ['selectedSiteId' => old('site_id', $excavatorRent->site_id), 'selectName' => 'site_id', 'tabindex' => 3])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'site_id'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="from_date" class="control-label"><b style="color: red;">* </b> From Date : </label>
                                                            <input type="text" class="form-control decimal_number_only" name="from_date" id="from_date" placeholder="From date" value="{{ old('from_date', $excavatorRent->from_date->format('d-m-Y')) }}" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'from_date'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="to_date" class="control-label"><b style="color: red;">* </b> To Date : </label>
                                                            <input type="text" class="form-control decimal_number_only" name="to_date" id="to_date" placeholder="To date" value="{{ old('to_date', $excavatorRent->to_date->format('d-m-Y')) }}" tabindex="5">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'to_date'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="description" class="control-label">Description: </label>
                                                    @if(!empty(old('description')))
                                                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description" style="resize: none;" tabindex="6" maxlength="200">{{ old('description') }}</textarea>
                                                    @else
                                                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description" style="resize: none;" tabindex="6" maxlength="200">{{ $excavatorRent->description }}</textarea>
                                                    @endif
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'description'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="total_rent" class="control-label"><b style="color: red;">* </b> Rent : </label>
                                                    <input type="text" class="form-control decimal_number_only" name="total_rent" id="total_rent" placeholder="Total Rent" value="{{ old('total_rent', $excavatorRent->rent) }}" maxlength="8" tabindex="7">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'total_rent'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><br>
                                <div class="clearfix"> </div><br>
                                <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-3">
                                        <button type="reset" class="btn btn-default btn-block btn-flat" tabindex="9">Clear</button>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-warning btn-block btn-flat update_button" tabindex="8">Update</button>
                                    </div>
                                    <!-- /.col -->
                                </div><br>
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
@endsection
@section('scripts')
    <script src="/js/registrations/excavatorRentRegistration.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection