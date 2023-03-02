@extends('../inc/layout')
 @section('content')
 <!-- Content Wrapper. Contains page content -->
 <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ $title }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ $breadcrumb }}</li>
            </ol>
          </div>
          <div class="col" style="margin-top:9px;">
            <a href="{{ url('product/create') }}">
                <button type="button" class="btn btn-primary">+ Add Product</button>
            </a>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <!-- /.card-header -->
              <div class="card-body">
                <table id="list-datatable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" style="border-collapse: collapse;" cellspacing="0">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Description</th>
                    <th class="no-sort"></th>
                  </tr>
                  </thead>
                  <tbody>
                  
                  </tbody>
                  <tfoot>
                  <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Description</th>
                    <th></th>
                  </tr>
                  </tfoot>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
@endsection

@push('custom-styles')
<!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

  <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
  <script src="{{ $cdn ?? asset('vendor/sweetalert/sweetalert.all.js')  }}"></script>

@endpush

@section('plugin')
  <!-- DataTables  & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<!-- <script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script> -->
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ $cdn ?? asset('vendor/sweetalert/sweetalert.all.js')  }}"></script>
@endsection

@push('custom-scripts')
<script>
    function deleteBtn(route){
        Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
			      url: route,
			      type: 'DELETE',
                  data : { 
                    _token: "{{ csrf_token() }}" 
                  },
			      success: function(result) {
			          if (result == 1) {
                        if (typeof window.crud != 'undefined') {
                            // Move to previous page in case of deleting the only item in table
                            if(window.crud.rows().count() === 1) {
                            window.crud.page("previous");
                            }
                            window.crud.draw(false);
                        }

                        Swal.fire(
                            'Deleted!',
                            'Your product has been deleted.',
                            'success'
                         )
			          	  
			          } else {
			            Swal.fire(
                            'Failed!',
                            'product cannot be deleted',
                            'error'
                         )	          	  
			          }
			      },
			      error: function(result) {
                      if(result.status != 500 && result.responseJSON != null && result.responseJSON.message != null && result.responseJSON.message.length != 0){
						  var defaultText = result.responseJSON.message;
                          Swal.fire(
                            'Failed!',
                            defaultText,
                            'error'
                         )
					  }

			      }
			  });
            }
        })
    }
</script>
<script>
  $(function () {
    window.crud = $('#list-datatable').DataTable({
        "paging": true,
        "ordering": true,
        "autoWidth": false,
        "responsive": true,
        processing: true,
        serverSide: true,
        ajax: "{{url('product/search')}}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'price', name: 'price' },
            { data: 'stock', name: 'stock' },
            { data: 'description', name:'description' },
            {data: 'action', orderable: false}
        ],
        "columnDefs": [{
            "targets": 'no-sort',
            "orderable": false,
            "order": []
        }]
    });
  });
</script>
@endpush

