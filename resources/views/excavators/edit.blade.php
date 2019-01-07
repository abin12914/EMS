@extends('layouts.app')
@section('title', 'Excavator Edit')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Edit
            <small>Excavator</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('excavator.index') }}"> Excavator</a></li>
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
                            <h3 class="box-title" style="float: left;">Excavator Details</h3>
                                <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                        </div><br>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form action="{{route('excavator.update', $excavator->id)}}" method="post" class="form-horizontal" autocomplete="off">
                            <div class="box-body">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="name" class="control-label"><b style="color: red;">* </b> Name: </label>
                                                    <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name', $excavator->name) }}" maxlength="100" tabindex="1">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'name'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="description" class="control-label"><b style="color: red;">* </b> Description: </label>
                                                    @if(!empty(old('description')))
                                                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description" style="resize: none;" tabindex="2">{{ old('description') }}</textarea>
                                                    @else
                                                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description" style="resize: none;" tabindex="2">{{ $excavator->description }}</textarea>
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
                                                    <label for="maker" class="control-label"><b style="color: red;">* </b> Maker: </label>
                                                    <input type="text" class="form-control" name="maker" id="maker" placeholder="Maker" value="{{ old('maker', $excavator->maker) }}" maxlength="100" tabindex="3">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'maker'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="capacity" class="control-label"><b style="color: red;">* </b> Capacity: </label>
                                                    <input type="text" class="form-control decimal_number_only" name="capacity" id="capacity" placeholder="Capacity" value="{{ old('capacity', $excavator->capacity) }}" maxlength="100" tabindex="4">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'capacity'])
                                                    @endcomponent
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="bucket_rate" class="control-label"><b style="color: red;">* </b> Bucket Rate: </label>
                                                    <input type="text" class="form-control decimal_number_only" name="bucket_rate" id="bucket_rate" placeholder="Bucket Rate" value="{{ old('bucket_rate', $excavator->bucket_rate) }}" maxlength="100" tabindex="5">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'bucket_rate'])
                                                    @endcomponent
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="breaker_rate" class="control-label"><b style="color: red;">* </b> Breaker Rate: </label>
                                                    <input type="text" class="form-control decimal_number_only" name="breaker_rate" id="breaker_rate" placeholder="Breaker Rate" value="{{ old('breaker_rate', $excavator->breaker_rate) }}" maxlength="100" tabindex="6">
                                                    {{-- adding error_message p tag component --}}
                                                    @component('components.paragraph.error_message', ['fieldName' => 'breaker_rate'])
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
                                        <button type="reset" class="btn btn-default btn-block btn-flat" tabindex="8">Clear</button>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-warning btn-block btn-flat update_button" tabindex="7">Update</button>
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