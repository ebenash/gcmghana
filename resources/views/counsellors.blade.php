@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Counsellors') }}</div>
                <div class="card-body">
                    <div class="btn-group card-option float-right mb-2">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Add Counsellor
                        </button>
                        <div class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                            <form><button type="button" class="dropdown-item" data-toggle="modal" data-target="#addCounsellorModal">Add Single Counsellor</button></form>
                            <form><button type="button" class="dropdown-item" data-toggle="modal" data-target="#addBulkCounsellorModal">Import Bulk Counsellors</button></form>
                        </div>
                    </div>
                    <table id="table_id" class="display">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($counsellors as $item)
                                <tr>
                                    <td>{{$item->name}}</td>
                                    <td>{{$item->phone}}</td>
                                    <td>{{$item->created_at}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div id="addCounsellorModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addCounsellorModalTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <form method="POST" action="{{route('store-counsellor')}}" enctype="multipart/form-data">
                                    @csrf 
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCounsellorModalTitle">Add New Counsellor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label class="label" for="name">Counsellor Name <span style="color:red">*</span></label>
                                            <input type="text" name="counsellor_name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="label" for="phone">WhatsApp Number <span style="color:red">*</span></label>
                                            <input type="tel" id="phone" name="phone" class="form-control phone-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="label" for="name">Email Address</label>
                                            <input type="email" name="email" class="form-control" placeholder="eg. james.doe@email.com">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn  btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn  btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="addBulkCounsellorModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addBulkCounsellorModalTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <form method="POST" action="{{route('bulk-counsellors')}}" enctype="multipart/form-data">
                                    @csrf 
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addGroupModalTitle">Add Bulk Counsellors</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                    
                                        <a href="{{asset('downloads/gcmtemplate.xlsx')}}">Download a Sample file</a>
                                        <div class="form-group">
                                            <input type="file" name="import_file" class="form-control" >
                                            <small id="importHelpBlock" class="form-text text-muted">Your Excel File</small>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-3">
                                            <input type="checkbox" class="custom-control-input" id="headers" name="headers" checked="checked"  >
                                            <label class="custom-control-label" for="headers">Headers on First Row</label><br/>
                                            <small>Select if the first row in the file contains the table headings.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn  btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn  btn-primary">Upload</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
