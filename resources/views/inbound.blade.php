<x-appjd-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="table table-bordered yajra-datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Vendor</th>
                                <th>EDI Code</th>
                                <th>Purchase Order Number</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Po ID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
    $(function () {
        var dt = $('.yajra-datatable').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"top"ifl<"pagination pull-right"p><"clear">>rt<"bottom"<"pagination pull-right"p><"clear">>',
            ajax: "{{ route('inbound.list') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'vendor',name: 'vendor'},
                {data: 'edi_code', name: 'code'},
                {data: 'po_number', name: 'Po'},
                {data: 'status', name: 'status'},
                {data: 'date', name: 'date'},
                {data: 'po_id', name: 'po_id'},
                {data: 'action', name: 'action'},
            ],
            columnDefs: [
                { targets: [0,6], visible: false},
                {"mRender": function ( data, type, row ) {
                    var content ='';
                    if(row['status'] == 'new' && row['edi_code'] == '850' && row['po_id'] == null){
                        content += '<p><a href="javascript:void(0)"   data-did="'+row['id']+'"  data-vendor="'+row['nick_name']+'"  data-action="translate_one" class="process btn btn-success btn-sm">Create Order</a></p>';
                    }
                    if(row['edi_code'] == '997'){
                        content += '<p><a href="javascript:void(0)"   data-did="'+row['id']+'"  data-vendor="'+row['nick_name']+'"  data-action="translate_one_997" class="process btn btn-success btn-sm">Check Response</a></p>';
                    }
                    return content;
                    },"aTargets": [7]
                }
            ],
            "drawCallback":function(settings){
                $('a.process').off().on('click', function(){
                    var params = {
                        'vendor':$(this).data('vendor'),
                        'action':$(this).data('action'),
                        'id':$(this).data('did'),
                    }
                    var r = confirm("Confirm?");
                    if (r == true) {
                        window.open('/'+params.action+'/'+params.id, '_blank');
                    } 
                    dt.ajax.reload(false,null,false);
                });
            }
        });
    });
    </script>
</x-appjd-layout>
