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
                                <th>Nick</th>
                                <th>Edi update</th>
                                <th>Purchase Order update</th>
                                <th>Order upate</th>
                                <th>Ack upate</th>
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
            ajax: "{{ route('vendor.list') }}",
            columns: [
                {data: 'id', name: 'DT_RowIndex'},
                {data: 'name',name: 'vendor'},
                {data: 'nick_name',name: 'nick'},
                {data: 'edi_updated_at', name: 'edi_update_at'},
                {data: 'po_updated_at', name: 'po_update_at'},
                {data: 'order_updated_at', name: 'order_update_at'},
                {data: 'ack_updated_at', name: 'ack_update_at'},
                {
                    data: 'action', 
                    name: 'action', 
                    orderable: true, 
                    searchable: true
                },
            ],
            columnDefs: [
                { targets: [2], visible: false},
                {"mRender": function ( data, type, row ) {
                    var content = '<p><a href="javascript:void(0)"   data-did="'+row['id']+'"  data-vendor="'+row['nick_name']+'"  data-action="inbox" class="check btn btn-success btn-sm">Check Inbox</a></p>';
                   
                        content += '<p><a href="javascript:void(0)"   data-did="'+row['id']+'"  data-vendor="'+row['nick_name']+'"  data-action="inbound" class="process btn btn-success btn-sm">Pull Edi</a></p>';
                        
                        return content;
               },"aTargets": [7]
               },
                
            ],
            "drawCallback":function(settings){

                $('a.check').off().on('click', function(){
                    var params = {
                        'vendor':$(this).data('vendor'),
                        'action':$(this).data('action'),
                        'id':$(this).data('did'),
                    }

                    var r = confirm("Confirm?");
                    if (r == true) {
                        window.open('/'+params.action+'/'+params.vendor, '_blank');
                    } 
                   
                    dt.ajax.reload(false,null,false);
                });

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
