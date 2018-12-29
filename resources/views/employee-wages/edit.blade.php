@extends('layouts.app')
@section('title', 'Edit Employee Wage')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Edit
            <small>Employee Wage</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('employee-wage.index') }}"> Employee Wage</a></li>
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
                            <h3 class="box-title" style="float: left;">Employee Wage Details</h3>
                                <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                        </div><br>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form action="{{route('employee-wage.update', $employeeWage->id)}}" method="post" class="form-horizontal" autocomplete="off">
                            <div class="box-body">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="employee_id" class="control-label"><b style="color: red;">* </b> Employee : </label>
                                                    {{-- adding employee select component --}}
                                                    @component('components.selects.employees', ['selectedEmployeeId' => old('employee_id', $employeeWage->employee_id), 'selectName' => 'employee_id', 'tabindex' => 1])
                                                    @endcomponent
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'employee_id'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="from_date" class="control-label"><b style="color: red;">* </b> From Date : </label>
                                                    <input type="text" class="form-control decimal_number_only datepicker_edit" name="from_date" id="from_date" placeholder="From date" value="{{ old('from_date', $employeeWage->from_date->format('d-m-Y')) }}" tabindex="2">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'from_date'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="to_date" class="control-label">To Date [Use only if generating wage for multiple days] : </label>
                                                    <input type="text" class="form-control decimal_number_only datepicker_edit" name="to_date" id="to_date" placeholder="To date" value="{{ old('to_date', $employeeWage->to_date->format('d-m-Y')) }}" tabindex="3">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'to_date'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="wage_amount" class="control-label"><b style="color: red;">* </b> Wage/Salary Amount: </label>
                                                    <input type="text" class="form-control decimal_number_only" name="wage_amount" id="wage_amount" placeholder="Bill amount" value="{{ old('wage_amount', $employeeWage->wage) }}" maxlength="8" tabindex="4">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'wage_amount'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="description" class="control-label"><b style="color: red;">* </b> Description: </label>
                                                    @if(!empty(old('description')))
                                                        <textarea class="form-control" name="description" id="description" rows="2" placeholder="Description" style="resize: none;" tabindex="5">{{ old('description') }}</textarea>
                                                    @else
                                                        <textarea class="form-control" name="description" id="description" rows="2" placeholder="Description" style="resize: none;" tabindex="5">{{ $employeeWage->description }}</textarea>
                                                    @endif
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'description'])
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
@section('scripts')
    <script src="/js/registrations/employeeWageRegistration.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection