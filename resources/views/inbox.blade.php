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
                    <h1>Inbox: {{$vendor}}</h1>
                    <table class="table table-bordered yajra-datatable">
                        <thead>
                            <tr>
                                <th>Vendor</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($files)
                                @foreach ($files as $file)
                                <tr>
                                    <td>{{$vendor}}</td>
                                    <td>{{$file['name']}}</td>
                                    <td></td>
                                </tr>
                                @endforeach
                            @else
                            @endif
                        </tbody>
                    </table>
                    <h1>Inbox: {{$vendor}} files removed:</h1>
                    @if ($del)
                        <ul>
                        @foreach ($del as $d)
                            <li>{{$d}}</li>
                        @endforeach
                        <ul>
                    @else
                    <p> NONE</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            
            var dt = $('.yajra-datatable').DataTable({
                columnDefs: [
                
                    {"mRender": function ( data, type, row ) {
                            var content ='';
                            content += '<p><a href="javascript:void(0)"   data-file="'+row['1']+'"  data-vendor="'+row[0]+'"  data-action="inbound_one" class="process btn btn-success btn-sm">Get EDI file</a></p>';
                        
                            return content;
                },"aTargets": [2]
                },
                ],
                "drawCallback":function(settings){
                    $('a.process').off().on('click', function(){
                        var params = {
                            'vendor':$(this).data('vendor'),
                            'action':$(this).data('action'),
                            'file':$(this).data('file'),
                        }
                        var r = confirm("Confirm?");
                        if (r == true) {

                            var u = '/'+params.action+'/'+params.vendor+'/'+encodeURIComponent(params.file);
                            window.open(u, '_blank');
                        } 
                    });
                }
            });
        });
    </script>
</x-appjd-layout>