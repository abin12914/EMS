@extends('layouts.app')
@section('title', 'Excavator Rent List')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Excavator Rent
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Excavator Rent List</li>
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
                        <form action="{{ route('excavator-rent.index') }}" method="get" class="form-horizontal" autocomplete="off">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label for="from_date" class="control-label">From Date : </label>
                                            <input type="text" class="form-control datepicker" name="from_date" id="from_date" value="{{ !empty(old('from_date')) ? old('from_date') : $params['from_date']['paramValue'] }}" tabindex="1">
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'from_date'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-4">
                                            <label for="to_date" class="control-label">To Date : </label>
                                            <input type="text" class="form-control datepicker" name="to_date" id="to_date" value="{{ !empty(old('to_date')) ? old('to_date') : $params['to_date']['paramValue'] }}" tabindex="2">
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'to_date'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-4">
                                            <label for="excavator_id" class="control-label">Excavator : </label>
                                            {{-- adding excavator select component --}}
                                            @component('components.selects.excavators', ['selectedExcavatorId' => $params['excavator_id']['paramValue'], 'selectName' => 'excavator_id', 'tabindex' => 3])
                                            @endcomponent
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'excavator_id'])
                                            @endcomponent
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label for="account_id" class="control-label">Customer : </label>
                                            {{-- adding account select component --}}
                                            @component('components.selects.accounts', ['selectedAccountId' => $params['account_id']['paramValue'], 'selectName' => 'account_id', 'cashAccountFlag' => false, 'activeFlag' => true, 'tabindex' => 4])
                                            @endcomponent
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'account_id'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-4">
                                            <label for="site_id" class="control-label">Site : </label>
                                            {{-- adding site select component --}}
                                            @component('components.selects.sites', ['selectedSiteId' => $params['site_id']['paramValue'], 'selectName' => 'site_id', 'tabindex' => 5])
                                            @endcomponent
                                            {{-- adding error_message p tag component --}}
                                            @component('components.paragraph.error_message', ['fieldName' => 'site_id'])
                                            @endcomponent
                                        </div>
                                        <div class="col-md-4">
                                            <label for="no_of_records" class="control-label">No Of Records Per Page : </label>
                                            {{-- adding no of records text component --}}
                                            @component('components.texts.no-of-records-text', ['noOfRecords' => $noOfRecords, 'tabindex' => 6])
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
                                    <button type="reset" class="btn btn-default btn-block btn-flat"  value="reset" tabindex="8">Clear</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat submit-button" tabindex="7"><i class="fa fa-search"></i> Search</button>
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
                                <h6 class="text-center">Excavator Rent List</h6>
                                <table class="table table-responsive table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 10%;">Excavator</th>
                                            <th style="width: 15%;">Customer</th>
                                            <th style="width: 10%;">Site</th>
                                            <th style="width: 10%;">From</th>
                                            <th style="width: 10%;">To</th>
                                            <th style="width: 20%;">Description</th>
                                            <th style="width: 10%;">Amount</th>
                                            <th style="width: 5%;" class="no-print">Edit</th>
                                            <th style="width: 5%;" class="no-print">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($excavatorRents))
                                            @foreach($excavatorRents as $index => $excavatorRent)
                                                <tr>
                                                    <td>{{ $index + $excavatorRents->firstItem() }}</td>
                                                    <td>{{ $excavatorRent->excavator->name }}</td>
                                                    <td>{{ $excavatorRent->transaction->debitAccount->account_name }}</td>
                                                    <td>{{ $excavatorRent->site->name }}</td>
                                                    <td>{{ $excavatorRent->from_date->format('d-m-Y') }}</td>
                                                    <td>{{ $excavatorRent->to_date->format('d-m-Y') }}</td>
                                                    <td>{{ $excavatorRent->description }}</td>
                                                    <td>{{ $excavatorRent->rent }}</td>
                                                    <td class="no-print">
                                                        <a href="{{ route('excavator-rent.edit', ['id' => $excavatorRent->id]) }}" style="float: left;">
                                                            <button type="button" class="btn btn-warning"><i class="fa fa-edit"></i> Edit</button>
                                                        </a>
                                                    </td>
                                                    <td class="no-print">
                                                        <form action="{{ route('excavator-rent.destroy', $excavatorRent->id) }}" method="post" class="form-horizontal">
                                                            {{ method_field('DELETE') }}
                                                            {{ csrf_field() }}
                                                            <button type="button" class="btn btn-danger delete_button"><i class="fa fa-trash"></i> Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if(Request::get('page') == $excavatorRents->lastPage() || $excavatorRents->lastPage() == 1)
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-red"><b>Total Amount</b></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-red"><b>{{ $totalExcavatorRent }}</b></td>
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
                                @if(!empty($excavatorRents))
                                    <div>
                                        Showing {{ $excavatorRents->firstItem(). " - ". $excavatorRents->lastItem(). " of ". $excavatorRents->total() }}<br>
                                    </div>
                                    <div class=" no-print pull-right">
                                        {{ $excavatorRents->appends(Request::all())->links() }}
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