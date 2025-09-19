<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>ID</th>
            <th>Quote</th>
            <th>Schedule Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>    
        @php $i=0; @endphp 
        @if(count($result) > 0) 
            @foreach($result as $row) 
                @php $i++; @endphp 
                <tr>
                    <td>{{$i}}</td>
                    <td>@if($row->quote!=''){{$row->quote}}@else -- @endif</td>
                    <td>@if($row->schedule_date!=''){{date('d-m-Y h:i A',strtotime($row->schedule_date))}}@else -- @endif</td>
                    <td> @if($row->status==1) <a href="{{url('admin/update-quote-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">Active</span>
                        </a> @else <a href="{{url('admin/update-quote-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">Inactive</span>
                        </a> @endif 
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> Edit </a>
                                <a class="dropdown-item" href="{{url('/admin/quote/translation/'.$row->id)}}">
                                <i class="ti ti-language me-1 margin-top-negative-4"></i> Translation </a>
                                <form action="{{ url('admin/delete-quote', $row->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete?')"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="Delete">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>Delete </button>
                                </form>
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">Edit Live News</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record" action="{{url('admin/update-quote')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateQuote();"> @csrf 
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <input type="hidden" name="id" value="{{$row->id}}">
                                            <label class="form-label" for="quote">Quote <span class="required">*</span></label>
                                            <textarea class="form-control" id="quote" name="quote" placeholder="Enter quote"  value="{{$row->quote}}">{{$row->quote}}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="schedule_date">Schedule Date <span class="required">*</span></label>
                                            <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM" id="flatpickr-datetime" name="schedule_date" readonly="readonly" value="{{date('Y-m-d H:i',strtotime($row->schedule_date))}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Edit</button>
                                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr> 
            @endforeach 
        @else 
            <tr>
                <td colspan="7" class="record-not-found">
                    <span>Record not found</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>