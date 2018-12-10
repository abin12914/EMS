@extends('layouts.app')
@section('title', 'Credit List')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Credit
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Credit List</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        @if(Session::has('message'))
            <div class="alert {{ Session::get('alert-class', 'alert-info') }}" id="alert-message">
                <h4>
                  {!! Session::get('message') !!}
                  <?php session()->forget('message'); ?>
                </h4>
            </div>
        @endif
        <!-- Main row -->
        <div class="row no-print">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Filter result</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-header">
                        <form action="{{ route('report.credit.list') }}" method="get" class="form-horizontal">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="col-sm-12 {{ !empty($errors->first('relation_type')) ? 'has-error' : '' }}">
                                            <label for="relation_type" class="control-label">Relation : </label>
                                            <select class="form-control select2" name="relation_type" id="relation_type" tabindex="7" style="width: 100%;">
                                                    <option value="" {{ empty(old('relation_type')) ? 'selected' : '' }}>Select primary relation type</option>
                                                    @if(!empty($relationTypes))
                                                        @foreach($relationTypes as $key => $relationType)
                                                            <option value="{{ $key }}" {{ (old('relation_type', $relation) == $key) ? 'selected' : '' }}>
                                                                {{ $relationType }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @if(!empty($errors->first('relation_type')))
                                                <p style="color: red;" >{{$errors->first('relation_type')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div><br>
                            <div class="row no-print">
                                <div class="col-md-4"></div>
                                <div class="col-md-2">
                                    <button type="reset" class="btn btn-default btn-block btn-flat"  value="reset" tabindex="10">Clear</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat submit-button" tabindex="4"><i class="fa fa-search"></i> Search</button>
                                </div>
                            </div>
                        </form>
                        <!-- /.form end -->
                    </div><br>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 2%;">#</th>
                                            <th style="width: 28%;">Account Name</th>
                                            <th style="width: 30%;">Account Holder/Head</th>
                                            <th style="width: 20%;">Debit</th>
                                            <th style="width: 20%;">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($accounts))
                                            @foreach($accounts as $index => $account)
                                                <?php
                                                if(empty($creditAmount[$account->id])) {
                                                    $creditAmount[$account->id] = 0;
                                                }
                                                if(empty($debitAmount[$account->id])) {
                                                    $debitAmount[$account->id] = 0;
                                                }
                                                ?>
                                                <tr>
                                                    <td>{{ ($index+1) }}</td>
                                                    <td>{{ $account->account_name }}</td>
                                                    <td>{{ $account->name }}</td>
                                                    @if($debitAmount[$account->id] > $creditAmount[$account->id])
                                                        <td>{{ round(($debitAmount[$account->id] - $creditAmount[$account->id]), 2) }}</td>
                                                        <td></td>
                                                    @elseif($creditAmount[$account->id] > $debitAmount[$account->id])
                                                        <td></td>
                                                        <td>{{ round(($creditAmount[$account->id] - $debitAmount[$account->id]), 2) }}</td>
                                                    @else
                                                        <td>-</td>
                                                        <td>-</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                        <tr>
                                            <td>#</td>
                                            <td></td>
                                            <td></td>
                                            <td>{{ !empty($totalDebitAmount) ? round($totalDebitAmount, 2) : 0 }}</td>
                                            <td>{{ !empty($totalCreditAmount) ? round($totalCreditAmount, 2) : 0 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row no-print">
                            <div class="col-md-12">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <div class="pull-right">
                                        {{-- @if(!empty($accounts))
                                            {{ $accounts->appends(Request::all())->links() }}
                                        @endif --}}
                                    </div>
                                </div>
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
