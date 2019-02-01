@extends('layouts.app')
@section('title', 'Excavator List')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Excavator
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Excavator List</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    {{-- page header for printers --}}
                    @include('sections.print-head')
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-center">Excavators List</h6>
                                <table class="table table-responsive table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 20%;">Name</th>
                                            <th style="width: 20%;">Description</th>
                                            <th style="width: 20%;">Maker & Capacity</th>
                                            <th style="width: 15%;">Bucket Rate</th>
                                            <th style="width: 15%;">Breaker Rate</th>
                                            <th style="width: 5%;" class="no-print">Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($excavators))
                                            @foreach($excavators as $index => $excavator)
                                                <tr>
                                                    <td>{{ $index + $excavators->firstItem() }}</td>
                                                    <td>{{ $excavator->name }}</td>
                                                    <td>{{ $excavator->description }}</td>
                                                    <td>{{ $excavator->maker. " : ". $excavator->capacity }}</td>
                                                    <td>{{ $excavator->bucket_rate }}</td>
                                                    <td>{{ $excavator->breaker_rate }}</td>
                                                    <td class="no-print">
                                                        <a href="{{ route('excavator.edit', ['id' => $excavator->id]) }}" style="float: left;">
                                                            <button type="button" class="btn btn-warning"><i class="fa fa-edit"></i> Edit</button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if(!empty($excavators))
                                    <div>
                                        Showing {{ $excavators->firstItem(). " - ". $excavators->lastItem(). " of ". $excavators->total() }}<br>
                                    </div>
                                    <div class=" no-print pull-right">
                                        {{ $excavators->appends(Request::all())->links() }}
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