@extends('layouts.app')
@section('title', 'Excavator Reading Registration')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Register
            <small>Excavator Reading</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('excavator-reading.index') }}"> Excavator Reading</a></li>
            <li class="active"> Registration</li>
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
                            <h3 class="box-title" style="float: left;">Excavator Reading Details</h3>
                                <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                        </div><br>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form action="{{route('excavator-reading.store')}}" method="post" class="form-horizontal" autocomplete="off">
                            <div class="box-body">
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="reading_date" class="control-label"><b style="color: red;">* </b> Date : </label>
                                                    <input type="text" class="form-control decimal_number_only datepicker_reg" name="reading_date" id="reading_date" placeholder="Reading date" value="{{ old('reading_date') }}" tabindex="1">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'reading_date'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="excavator_id" class="control-label"><b style="color: red;">* </b> Excavator : </label>
                                                    {{-- adding excavator select component --}}
                                                    @component('components.selects.excavators', ['selectedExcavatorId' => old('excavator_id'), 'selectName' => 'excavator_id', 'tabindex' => 2])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'excavator_id'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="customer_account_id" class="control-label"><b style="color: red;">* </b> Customer : </label>
                                                    {{-- adding account select component --}}
                                                    @component('components.selects.accounts', ['selectedAccountId' => old('customer_account_id'), 'cashAccountFlag' => true, 'selectName' => 'customer_account_id', 'activeFlag' => true, 'tabindex' => 3])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'customer_account_id'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="site_id" class="control-label"><b style="color: red;">* </b> Site : </label>
                                                    {{-- adding excavator select component --}}
                                                    @component('components.selects.sites', ['selectedSiteId' => old('site_id'), 'selectName' => 'site_id', 'tabindex' => 4])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'site_id'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="employee_id" class="control-label"><b style="color: red;">* </b> Operator : </label>
                                                    {{-- adding excavator select component --}}
                                                    @component('components.selects.employees', ['selectedEmployeeId' => old('employee_id'), 'selectName' => 'employee_id', 'tabindex' => 5])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'employee_id'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="description" class="control-label">Description: </label>
                                                    @if(!empty(old('description')))
                                                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description" style="resize: none;" tabindex="6">{{ old('description') }}</textarea>
                                                    @else
                                                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description" style="resize: none;" tabindex="6"></textarea>
                                                    @endif
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'description'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="bucket_hour" class="control-label"><b style="color: red;">* </b> Hours Worked [Bucket] : </label>
                                                            <input type="text" class="form-control decimal_number_only" name="bucket_hour" id="bucket_hour" placeholder="Hours Worked [Bucket]" value="{{ old('bucket_hour') }}" maxlength="7" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'bucket_hour'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="bucket_rate" class="control-label"><b style="color: red;">* </b> Hourly Rate [Bucket] : </label>
                                                            <input type="text" class="form-control decimal_number_only" name="bucket_rate" id="bucket_rate" placeholder="Hourly Rate [Bucket]" value="{{ old('bucket_rate') }}" maxlength="8" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'bucket_rate'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="breaker_hour" class="control-label"><b style="color: red;">* </b> Hours Worked [Breaker] : </label>
                                                            <input type="text" class="form-control decimal_number_only" name="breaker_hour" id="breaker_hour" placeholder="Hours Worked [Breaker]" value="{{ old('breaker_hour') }}" maxlength="8" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'breaker_hour'])
                                                            @endcomponent
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="breaker_rate" class="control-label"><b style="color: red;">* </b> Hourly Rate [Breaker] : </label>
                                                            <input type="text" class="form-control decimal_number_only" name="breaker_rate" id="breaker_rate" placeholder="Hourly Rate [Breaker]" value="{{ old('breaker_rate') }}" maxlength="8" tabindex="4">
                                                            {{-- adding error_message p tag component --}}
                                                            @component('components.paragraph.error_message', ['fieldName' => 'breaker_rate'])
                                                            @endcomponent
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="total_rent" class="control-label"><b style="color: red;">* </b> Total Rent : </label>
                                                    <input type="text" class="form-control decimal_number_only" name="total_rent" id="total_rent" placeholder="Total Rent" value="{{ old('total_rent') }}" maxlength="8" tabindex="4">
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
                                        <button type="reset" class="btn btn-default btn-block btn-flat" tabindex="7">Clear</button>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary btn-block btn-flat submit-button" tabindex="6">Submit</button>
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