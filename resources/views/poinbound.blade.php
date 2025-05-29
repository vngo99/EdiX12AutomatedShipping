<x-appjd-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-center items-center">
            <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full" role="status">
                <span class="visually-hidden"></span>
            </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex -mx-3 mb-1">
                            <div class="w-full md:w-1/2 px-3 mb-1 md:mb-0"></div>
                    
                            <div class="w-full md:w-1/3 px-3 mb-1 md:mb-0">
                        
                                <div class="category-filter">
                                    <select id="status_filter" class="form-control">
                                        <option value="all">Show All</option>
                                        <option value="new">New</option>
                                        <option value="Ordered">Ordered</option>
                                        <option value="PO Ack">Ack</option>
                                        <option value="Shipped">Shipped</option>
                                        <option value="Shipment Notice">Shipment Notice</option>
                                        <option value="oos">OOS</option>
                                        <option value="bad address">Bad Address</option>
                                        <option value="Invoice Notice DI">Invoice</option>
                                        <option value="Tracking Error">Tracking Number Error</option>
                                        <option value="PO Delay">PO Delay</option>
                                    </select>
                                </div>
                            </div>
                            <div class="w-full md:w-1/2 px-3 mb-1 md:mb-0"></div>
                    </div>
                </div>
                    <table id="potable" class="table table-bordered yajra-datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Vendor</th>
                                <th>EDI Code</th>
                                <th>Purchase Order type</th>
                                <th>Order</th>
                                <th>Shipping</th>
                                <th>Date</th>
                                <th>Address</th>
                                <th>Street</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Country</th>
                                <th>Zip</th>
                                <th>Status</th>
                                <th>ship date</th>
                                <th>ship message</th>
                                <th>inbound_id</th>
                                <th>shipped date</th>
                                <th>tracking_number</th>
                                <th>order_id</th>
                                <th>street2</th>
                                <th>phone</th>
                                <th>Items</th>
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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            ...
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary">Submit</button>
        </div>
        </div>
    </div>
    </div>
    <script type="text/javascript">
    $(function () {
        var dt = $('.yajra-datatable').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            lengthMenu: [ 10, 25, 50, 75, 100, 200, 300, 400],
            dom: '<"top"ifl<"pagination pull-right"p><"clear">>rt<"bottom"<"pagination pull-right"p><"clear">>',
            ajax:{
                "url":  "{{ route('poinbound.list') }}",
                "dataType": "json",
                "type": "GET",
                "data":function ( d ) {
                    return $.extend( {}, d, {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        'status_filter':$('#status_filter option:selected').val(),
                        
                    } );
                }
            },
            columns: [
                {data: 'id', name: 'DT_RowIndex'},
                {data: 'vendor',name: 'vendor'},
                {data: 'edi_code', name: 'edi_code'},
                {data: 'po_type', name: 'po_type'},
                {data: 'po_number', name: 'po_number'},
                {data: 'shipping_via', name: 'shipping_via'},
                {data: 'date', name: 'date'},
                {data: 'name', name: 'name', width:'30%'},
                {data: 'street', name: 'street'},
                {data: 'city', name: 'city'},
                {data: 'state', name: 'state'},
                {data: 'country', name: 'country'},
                {data: 'zip', name: 'zip'},
                {data: 'status', name: 'status'},
                {data: 'shipping_date', name: 'shipdate'},
                {data: 'shipping_message', name: 'shipmessage'},
                {data: 'inbound_id', name: 'inbound_id'},
                {data: 'shipped_date', name: 'shipped_date'},
                {data: 'tracking_number', name: 'tracking_number'},
                {data: 'order_id', name: 'order_id'},
                {data: 'street2', name: 'street2'},
                {data: 'phone', name: 'phone'},
                {data: 'items', name: 'items'},
                {
                    data: 'action', 
                    name: 'action', 
                    orderable: true, 
                    searchable: true
                },
            ],
            columnDefs: [
                { targets: [0,1,2,3,5,6,8,9,10,11,12,14,15,16,17,18,19,20,21], visible: false},
                {"mRender": function ( data, type, row ) {
                   
                   var content = '';
                   if(data){

                    if(row['status'] == 'Shipment Notice'){
                        content +='<p><input class="bulk" value="'+row["inbound_id"]+'" type="checkbox" checked/></p>';
                    }
                        content +='<p>'+row['vendor']+'<p/>';
                       content +='<p>'+data+'<p/>';
                       content +='<p>'+row['po_type']+'<p/>';
                       content +='<p>'+row['date']+'<p/>';
                       content +='<p>'+row['id']+'<p/>';
                   }
                   return content;
               },"aTargets": [4]
               },
               {"mRender": function ( data, type, row ) {
                   
                   var content = '';
                   if(data){
                        
                       content +='<p>'+data+'<p/>';
                       content +='<p>'+row['shipping_message']+'<p/>';
                       content +='<p>'+row['shipping_date']+'<p/>';
                   }
                   return content;
               },"aTargets": [13]
               },
                {"mRender": function ( data, type, row ) {
                   
                    var content = '';
                    if(data){

                        content +=formatAddress(data, row);
                    }
                    return content;
                },"aTargets": [7]
                },
                {"mRender": function ( data, type, row ) {
                   
                    var content = '';
                    if(row['shipped_date']){
                        content += '<p>Shipped Date:'+row['shipped_date']+'</p>';
                    }
                    if(row['tracking_number']){
                        content += '<p>Tracking Number:'+row['tracking_number']+'</p>';
                    }
                    if(data){

                        content +=formatItems(data, row['po_type']);
                    }
                    return content;
                },"aTargets": [22]
                },
                {"mRender": function ( data, type, row ) {

                    var content ='';
                    if(row['po_type'] == 'Stand-alone Order'){

                        if(row['status'] == 'new' || row['status'] == 'bad address' || row['status'] == 'oos'  ){
                            content += '<p><a href="javascript:void(0)"  data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_ack_change" class="process btn btn-success btn-sm">Stocking Ack EDI</a></p>';
                        
                        }

                        if(row['status'] == 'PO Ack'){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="create_teapplix_order_po" class="process btn btn-success btn-sm">Stocking Create Teapplix Order</a></p>';
                            content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_tracking_update" class="process btn btn-success btn-sm">Check Tracking</a></p>';

                          
                        }

                        if(row['status'] == 'Ordered'){
                            
                                content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_tracking_update" class="process btn btn-success btn-sm">Check Tracking</a></p>';
                                content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="shipment_notice_change" class="process btn btn-success btn-sm">Stocking Shipment Notice EDI</a></p>';
                        
                           
                           
                        }

                        if(row['status'] == 'Shipment Notice'){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="invoice_notice_change_di" class="process btn btn-success btn-sm">Stocking Invoice Debit EDI</a></p>';
                           
                        }

                        content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="create_teapplix_order_po" class="process btn btn-success btn-sm">Stocking Create Teapplix Order</a></p>';
                        content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="shipment_notice_change" class="process btn btn-success btn-sm">Stocking Shipment Notice EDI</a></p>';
                        content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="invoice_notice_change_di" class="process btn btn-success btn-sm">Stocking Invoice Debit EDI</a></p>';
                        content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_reject" class="process btn btn-success btn-sm">Reject EDI</a></p>';
                    
                    }else{

                        if(row['status'] == 'new' || row['status'] == 'bad address' || row['status'] == 'oos'  ){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="create_teapplix_order_po" class="process btn btn-success btn-sm">Create Teapplix Order</a></p>';
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_reject" class="process btn btn-success btn-sm">Reject EDI</a></p>';
                            content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="fix_sku" class="process btn btn-success btn-sm">Fix Sku</a></p>';

                            if(row['vendor'] == 'autoany'){
                                content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_delay_autoany" class="process btn btn-success btn-sm">Delay EDI</a></p>';
 
                            }
                        
                        }

                        if(row['status'] == 'PO Ack'){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_reject" class="process btn btn-success btn-sm">Reject EDI</a></p>';
                    

                        }
                        if(row['order_id'] && row['status'] == 'PO Ack'){
                           

                            content += '<p><a href="javascript:void(0)"    data-did="'+row['order_id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_shipping_status" class="process btn btn-success btn-sm">Check Shipping</a></p>';
                        
                          
                        }
                        if(row['status'] == 'Ordered'){

                            if(row['vendor'] !='autoany'){

                                content += '<p><a href="javascript:void(0)"  data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_ack" class="process btn btn-success btn-sm">Ack EDI</a></p>';
                            }else{
                                content += '<p><a href="javascript:void(0)"    data-did="'+row['order_id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_shipping_status" class="process btn btn-success btn-sm">Check Shipping</a></p>';
                        
                            }
                        }
                        if(row['status'] == 'Shipped'){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="shipment_notice_all" class="process btn btn-success btn-sm">Shipment Notice EDI</a></p>';
                            content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_tracking_update" class="process btn btn-success btn-sm">Check Tracking</a></p>';
                        
                        }
                        if(row['status'] == 'Shipment Notice'){
                                content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="invoice_notice_di_all" class="process btn btn-success btn-sm">Invoice Debit EDI</a></p>';
                                content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_reject" class="process btn btn-success btn-sm">Reject EDI</a></p>';
                                content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_tracking" class="process btn btn-success btn-sm">Check Tracking</a></p>';
                        }
                        if(row['status'] == 'Invoice Notice DI'){
                           
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="shipment_notice_all_ns" class="process btn btn-success btn-sm">Shipment Notice EDI</a></p>';
                           
                        }
                        content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_address" class="process btn btn-success btn-sm">Check Address</a></p>';
                        if(row['status'] == 'Tracking Error'){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_reject" class="process btn btn-success btn-sm">Reject EDI</a></p>';
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="invoice_notice_di_all" class="process btn btn-success btn-sm">Invoice Debit EDI</a></p>';
                            content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="check_tracking_update" class="process btn btn-success btn-sm">Check Tracking</a></p>';

                            if(row['vendor'] =='turn5'){
                                 content += '<p><a href="javascript:void(0)"    data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="invoice_notice_cn_all" class="process btn btn-success btn-sm">Invoice Credit EDI</a></p>';
                            }
                        }
                        if(row['status'] == 'PO Delay'){
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"  data-vendor="'+row['vendor']+'"  data-action="create_teapplix_order_po" class="process btn btn-success btn-sm">Create Teapplix Order</a></p>';
                            content += '<p><a href="javascript:void(0)"   data-did="'+row['inbound_id']+'"   data-vendor="'+row['vendor']+'"  data-action="purchase_order_reject" class="process btn btn-success btn-sm">Reject EDI</a></p>';
                            content += '<p><a href="javascript:void(0)"    data-did="'+row['id']+'"   data-vendor="'+row['vendor']+'"  data-action="fix_sku" class="process btn btn-success btn-sm">Fix Sku</a></p>';
                        }
                    }
                    return content;
              },"aTargets": [23]
              }
            ],
            "drawCallback":function(settings){
                $('.animate-spin').hide();
                $("select#status_filter").on('change', function(){
                    dt.ajax.reload(false,null,false);
                });
                delayInputFilter();
                $('a.process').off().on('click', function(){
                    var params = {
                        'vendor':$(this).data('vendor'),
                        'action':$(this).data('action'),
                        'id':$(this).data('did'),
                    }
                    var r = confirm("Confirm?");
                    if (r == true) {
                        setTimeout(function(){
                            dt.ajax.reload(false,null,false);
                        }, 5000); 

                        window.open('/'+params.action+'/'+params.id, '_blank');
                    } 
                });

                $('a.bulk-invoice').off().on('click', function(){
                    var r = confirm("Confirm?");
                    if (r == true) {
                        var fcheck = $('input.bulk:checked');
                        var ids = [];
                        fcheck.each(function(){
                            ids.push($(this).val())
                        });

                        if(ids.length == 0){
                            alert("please select PO for invoice..")
                        }else{
                            var that = this;
                            $('.animate-spin').show();

                            $.ajaxSetup({
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                            $.post('/poinbound_services',{action:'bulkinvoice', data:ids})
                                    .done(function(resp){
                                    if(resp.success){
                                        alert(resp.response);
                                    }else{
                                        alert(resp.response);
                                    }
                                    $('.animate-spin').hide();  
                            });
                        }
                    }
                  
                });

                $('input.edit').off().on('change', function(){
                    $.ajaxSetup({
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var data ={
                        'id': $(this).data('did'),
                        'field':$(this).data('field'),
                        'value':$(this).val()
                    }
                    var that = this;
                    $.post('/poinbound_services',{action:'edit_address', data:data})
                            .done(function(resp){
                               if(resp.success){
                                setTimeout(function(){
                                        $(that).css({border:'2px solid green'});
                                    }, 500);
                               }else{
                                   alert(resp.response);
                               }
                            });
                });
                $('input.edit-aqty').off().on('change', function(){
                    $.ajaxSetup({
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var data ={
                        'id': $(this).data('did'),
                        'field':$(this).data('field'),
                        'value':$(this).val()
                    }
                    $.post('/poinbound_services',{action:'item_edit', data:data})
                            .done(function(resp){
                            if(resp.success){
                                alert(resp.response);
                            }else{
                                alert(resp.response);
                            }
                    });
                });
            }
        });

        function delayInputFilter(){
            var searchDelay = null;
            $('div.dataTables_filter input').off().on('change', function() {
                var search = $('div.dataTables_filter input').val();
                clearTimeout(searchDelay);
                searchDelay = setTimeout(function() {
                    if (search != null) {
                        if(typeof dt.fnFilter =='undefined'){
                            dt.search(search).draw();
                        }else{
                            dt.fnFilter(search);
                        }
                    }
                }, 1);
            });
        }

        function formatItems ( d, type ) {
            var content= '<table cellpadding="2" cellspacing="0" style="margin-left:30px;" class="table-striped table-bordered">'+
                '<thead>' +
                '<tr>'+
                '<th>Sku</th>'+
                '<th>Part#</th>'+
                '<th>Quantity</th>';
                if(type =='Stand-alone Order'){
                    content += '<th>Actual Qty</th>';
                }
                content += '<th>Price</th>'+
                    '<th>Description</th>'+
                    '</tr>' +
                    '</thead>'+
                    '<tbody>';

                var arrayLength = d.length;
                for (var i = 0; i < arrayLength; i++) {
                    content += '<tr>';
                    content += '<td>'+d[i].sku+'</td>';
                    content += '<td>'+d[i].part_number+'</td>';
                    content += '<td>'+d[i].quantity+'</td>';

                    if(type =='Stand-alone Order'){

                        if(d[i].actual_quantity > 0){
                            content += '<td><input style="width:5em" class="w-full rounded edit-aqty" type="number" min="0" maxlength="5" data-did="'+d[i].id+'" data-field="actual_quantity" value="'+d[i].actual_quantity+'"></td>';
                  
                        }else{
                            content += '<td><input style="width:5em" class="w-full rounded edit-aqty" type="number" min="0" maxlength="5" data-did="'+d[i].id+'" data-field="actual_quantity" value="'+d[i].quantity+'"></td>';
                  
                        }
                       
                    }

                    content += '<td>'+d[i].price+'</td>';
                    content += '<td>'+d[i].description+'</td>';
                    content += '</tr>';
                }
            content +='</tbody>'+
                '</table>';
            return content;
        }
        function formatAddress(d,r){
            var content = '';
                content +='<p>'+r['shipping_via']+'</p>';
                content +='<p>'+d+'</p>';
                content +='<form class="w-full max-w-lg">';
                content +='<div class="flex flex-wrap -mx-3 mb-2">';
                content +='      <div class="w-full md:w-1/1 px-3 mb-61md:mb-0">';
                content +='         <label class="mb-1" for="grid-city">';
                content +='          Street';
                content +='         </label>';
                content +='         <input data-did="'+r['id']+'" data-field="street" class="w-full rounded edit" id="grid-city" type="text" placeholder="" value="'+r['street']+'" >';
                content +='     </div>';
                if(r['street2'] !== null){
                    content +='      <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">';
                    content +='         <label class="mb-1" for="grid-city">';
                    content +='          Street2';
                    content +='         </label>';
                    content +='         <input data-did="'+r['id']+'" data-field="street2" class="w-full rounded edit" id="grid-city" type="text" placeholder="street2" value="'+r['street2']+'" >';
                    content +='     </div>';
                }
                content +='</div>';
                content +='<div class="flex -mx-3 mb-1">';
                content +='     <div class="w-full md:w-1/2 px-3 mb-1 md:mb-0">';
                content +='         <label class="mb-1" for="grid-city">';
                content +='          City';
                content +='         </label>';
                content +='         <input data-did="'+r['id']+'" data-field="city"   class="w-full rounded edit" id="grid-city" type="text" placeholder="Albuquerque" value="'+r['city']+'" >';
                content +='     </div>';
                content +='     <div class="w-full md:w-1/5 px-3 mb-1 md:mb-0">';
                content +='         <label class="mb-1" for="grid-city">';
                content +='          State';
                content +='         </label>';
                content +='         <input  data-did="'+r['id']+'" data-field="state"  class="w-full rounded edit" id="grid-city" type="text" placeholder="Albuquerque" value="'+r['state']+'" >';
                content +='     </div>';
                content +='     <div class="w-full md:w-1/3 px-3 mb-1 md:mb-0">';
                content +='         <label class="mb-1" for="grid-zip">';
                content +='         Zip';
                content +='          </label>';
                content +='         <input  data-did="'+r['id']+'" data-field="zip"  class="w-full rounded edit" id="grid-zip" type="text" placeholder="90210"  value="'+r['zip']+'" >';
                content +='      </div>';
                content +='</div>';

                content +='<div class="flex flex-wrap -mx-3 mb-1">';
                content +='     <div class="w-full md:w-1/2 px-3 mb-1 md:mb-0">';
                content +='         <label class="mb-1" for="grid-city">';
                content +='          Country';
                content +='         </label>';
                content +='         <input  data-did="'+r['id']+'" data-field="country"  class="w-full rounded edit" id="grid-city" type="text" placeholder="Albuquerque" value="'+r['country']+'" disabled>';
                content +='     </div>';
                content +='</div>';

                if(r['phone']){
                    content +='<div class="flex flex-wrap -mx-3 mb-1">';
                    content +='     <div class="w-full md:w-1/2 px-3 mb-1 md:mb-0">';
                    content +='         <label class="mb-1" for="grid-city">';
                    content +='          Phone';
                    content +='         </label>';
                    content +='         <input class="w-full rounded" id="grid-city" type="text" placeholder="Albuquerque" value="'+r['phone']+'" disabled>';
                    content +='     </div>';
                    content +='</div>';

                }
                content +='</form>';
            return content;
        }
        
    });
    </script>
</x-appjd-layout>
