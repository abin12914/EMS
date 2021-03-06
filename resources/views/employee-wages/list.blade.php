@extends('layouts.app')
@section('title', 'EmployeeWage List')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            EmployeeWage
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">EmployeeWage List</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row  no-print">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Filter List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-header">
                        <form action="{{ route('employee-wage.index') }}" method="get" class="form-horizontal" autocomplete="off">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label for="from_date" class="control-label">From Date : </label>
                                            <input type="text" class="form-control" name="from_date" id="from_date" value="{{ !empty(old('from_date')) ? old('from_date') : $params['from_date']['paramValue'] }}" tabindex="1">
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'from_date'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-3">
                                            <label for="to_date" class="control-label">To Date : </label>
                                            <input type="text" class="form-control" name="to_date" id="to_date" value="{{ !empty(old('to_date')) ? old('to_date') : $params['to_date']['paramValue'] }}" tabindex="2">
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'to_date'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-3">
                                            <label for="employee_id" class="control-label">Employee : </label>
                                            {{-- adding employee select component --}}
                                            @component('components.selects.employees', ['selectedEmployeeId' => $params['employee_id']['paramValue'], 'selectName' => 'employee_id', 'tabindex' => 3])
                                            @endcomponent
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'employee_id'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-3">
                                            <label for="no_of_records" class="control-label">No Of Records Per Page : </label>
                                            {{-- adding no of records text component --}}
                                            @component('components.texts.no-of-records-text', ['noOfRecords' => $noOfRecords, 'tabindex' => 4])
                                            @endcomponent
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'no_of_records'])
                                            @endcomponent
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div><br>
                            <div class="row">
                                <div class="col-md-4"></div>
                                <div class="col-md-2">
                                    <button type="reset" class="btn btn-default btn-block btn-flat"  value="reset" tabindex="6">Clear</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat submit-button" tabindex="5"><i class="fa fa-search"></i> Search</button>
                                </div>
                            </div>
                        </form>
                        <!-- /.form end -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    {{-- page header for printers --}}
                    @include('sections.print-head')
                    <div class="box-header no-print">
                        @foreach($params as $param)
                            @if(!empty($param['paramValue']))
                                <b>Filters applied!</b>
                                @break
                            @endif
                        @endforeach
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-center">EmployeeWages List</h6>
                                <table class="table table-responsive table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 10%;">Transaction Date</th>
                                            <th style="width: 20%;">Employee</th>
                                            <th style="width: 20%;">Date</th>
                                            <th style="width: 20%;">Notes</th>
                                            <th style="width: 15%;">Amount</th>
                                            <th style="width: 5%;" class="no-print">Edit</th>
                                            <th style="width: 5%;" class="no-print">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($employeeWages))
                                            @foreach($employeeWages as $index => $employeeWage)
                                                <tr>
                                                    <td>{{ $index + $employeeWages->firstItem() }}</td>
                                                    <td>{{ $employeeWage->transaction->transaction_date->format('d-m-Y') }}</td>
                                                    <td>{{ $employeeWage->employee->account->name }}</td>
                                                    <td>{{ $employeeWage->from_date->format('d-m-Y'). " -to- ". $employeeWage->to_date->format('d-m-Y') }}</td>
                                                    <td>{{ $employeeWage->description }}</td>
                                                    <td>{{ $employeeWage->wage }}</td>
                                                    <td class="no-print">
                                                        <a href="{{ route('employee-wage.edit', $employeeWage->id) }}" style="float: left;">
                                                            <button type="button" class="btn btn-warning"><i class="fa fa-edit"></i> Edit</button>
                                                        </a>
                                                    </td>
                                                    <td class="no-print">
                                                        <form action="{{ route('employee-wage.destroy', $employeeWage->id) }}" method="post" class="form-horizontal">
                                                            {{ method_field('DELETE') }}
                                                            {{ csrf_field() }}
                                                            <button type="button" class="btn btn-danger delete_button"><i class="fa fa-trash"></i> Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if(Request::get('page') == $employeeWages->lastPage() || $employeeWages->lastPage() == 1)
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-red"><b>Total Amount</b></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-red"><b>{{ $totalEmployeeWage }}</b></td>
                                                    <td class="no-print"></td>
                                                    <td class="no-print"></td>
                                                </tr>
                                            @endif
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if(!empty($employeeWages))
                                    <div>
                                        Showing {{ $employeeWages->firstItem(). " - ". $employeeWages->lastItem(). " of ". $employeeWages->total() }}<br>
                                    </div>
                                    <div class=" no-print pull-right">
                                        {{ $employeeWages->appends(Request::all())->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.boxy -->
            </div>
            <!-- /.col-md-12 -->
        </div>
        <!-- /.row (main row) -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(function () {
            $('#from_date').datepicker({
                format: 'dd-mm-yyyy',
                endDate: '+0d',
                autoclose: true,
            }).on('changeDate', function(e) {
                    var selectedDate = new Date(e.date);
                    var msecsInADay = 86400000;
                    var endDate = new Date(selectedDate.getTime() + msecsInADay);
                    
                   //Set Minimum Date of EndDatePicker After Selected Date of StartDatePicker
                    $("#to_date").datepicker("setStartDate", endDate );
                });

            $('#to_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                endDate: '+0d',
            });

            $('body').on("change keypress", "#from_date", function (evt) {
                $('#to_date').datepicker('setDate', '');
            });
        });
    </script>
@endsection