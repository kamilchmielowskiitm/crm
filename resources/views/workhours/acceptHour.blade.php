@extends('layouts.main')
@section('style')
    <link href="{{ asset('/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
    <style>
        td{
            text-align: center;
        }

        #register_stop
        {
            float: left;
            width: 75px;
            height: 28px;
            margin-top: 3px;
        }
        #register_start
        {
            width: 75px;}
        .modifydate{
            margin-bottom: 0px;
            height: 41px;
            display: flow-root !important;
        }

    </style>
@endsection
@section('content')


    {{--Header page --}}
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <div class="alert gray-nav ">Godziny / Akceptacja Godzin</div>
            </div>
        </div>
    </div>

    <div id="success_div" style="display: none;" class='alert alert-success'>Godziny zostały zaakceptowane!</div>



    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default"  id="panel1">
                <div class="panel-heading">
                    <a data-toggle="collapse" data-target="#collapseOne">
                        Zakres wyszukiwania:
                    </a>
                </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="start_stop">
                                    <div id="collapseOne" class="panel-collapse collapse in">
                                        <div class="panel-body">
                                            <div class="form-group col-md-6">
                                                <label for ="ipadress">Data od:<span style="color:red;">*</span></label>
                                                <div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="datak" style="width:100%;">
                                                    <input  onchange="myFunction()"  id="start_date" class="form-control" name="od" type="text" value="{{date("Y-m-d")}}" readonly >
                                                    <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for ="ipadress">Data do:<span style="color:red;">*</span></label>
                                                <div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="datak" style="width:100%;">
                                                    <input onchange="myFunction()" id="stop_date" class="form-control" name="do" type="text" value="{{date("Y-m-d")}}"readonly >

                                                    <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                              <div class="col-md-12">
                                                <div class="checkbox-container">
                                                  <input type="checkbox" name="checkbox1" id="checkbox1" style="display:inline-block">
                                                  <label for="checkbox1"> Pokaż <em>tylko</em> użytkowników z niezarejestrowanym czasem pracy</label>
                                                </div>
                                              </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="panel panel-default"  id="panel2">
                <div class="panel-heading">
                    Godziny pracy:
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12 table-responsive">
                                    <table id="datatable"class="thead-inverse table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Osoba</th>
                                            <th>Start</th>
                                            <th>Zarejestrowane</th>
                                            <th>Modyfikacja</th>
                                            <th>Suma</th>
                                            <th>Akcja</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/js/dataTables.bootstrap.min.js')}}"></script>
    <script>

        var table;
        var givenCheckbox;

        function myFunction() {
            table.ajax.reload();
        }

        $('.form_date').datetimepicker({
            language:  'pl',
            autoclose: 1,
            minView : 2,
            pickTime: false,
        });

        $(document).ready( function () {
          var intValue;

            table = $('#datatable').DataTable({
                "autoWidth": false,
                "processing": true,
                "serverSide": true,
                "drawCallback": function( settings ) {

                    $('.form_time').datetimepicker({
                        language:  'pl',
                        weekStart: 1,
                        todayBtn:  1,
                        autoclose: 1,
                        todayHighlight: 1,
                        startView: 1,
                        minView: 0,
                        maxView: 1,
                        forceParse: 0
                    });

                },
                "ajax": {
                    'url': "{{ route('api.acceptHour') }}",
                    'type': 'POST',
                    'data': function ( d ) {
                        d.start_date = $('#start_date').val();
                        d.stop_date = $('#stop_date').val();
                        /*
                        *withCheck sprawdza, czy pole checkboxa zostało zaznaczone
                        */
                        d.withCheck = function() {
                          givenCheckbox = $('#checkbox1');
                          if (givenCheckbox.is(':checked')) {
                            intValue = 1;
                            return intValue;
                          }
                          else {
                            intValue = 0;
                            return intValue;
                          }
                        };
                    },
                    'headers': {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Polish.json"
                },
                "columns":[
                    {"data": "date"},

                    {"data":function (data, type, dataToSet) {
                        return data.first_name + " " + data.last_name;
                    },"name": "users.last_name"},

                    {"data": function (data, type, dataToSet) {
                        if(data.click_start == null)
                            data.click_start = "Brak infromacji";
                        if(data.click_stop == null)
                            data.click_stop = "Brak infromacji";
                        return data.click_start + "</br><span class='fa fa-arrow-circle-o-down fa-fw'></span> </br> " + data.click_stop;
                    },"name": "click_start" },

                    {"data": function (data, type, dataToSet) {
                        if(data.register_start == null)
                            data.register_start = "Brak infromacji";
                        if(data.register_stop == null)
                            data.register_stop = "Brak infromacji";
                        return "<span name='user_register_start'>"+data.register_start+"</span></br><span class='fa fa-arrow-circle-o-down fa-fw'></span> </br> <span name='user_register_stop'>" + data.register_stop + "</span>";
                    },"name": "register_start"},

                    {"data":null,"targets": -3,"orderable": false, "searchable": false },


                    {"data": "time", "name": "time","searchable": false },

                    {"data":null,"targets": -1,"orderable": false, "searchable": false }

                ],
                "columnDefs": [ {
                    "targets": -1,
                    "data": "id",
                    "defaultContent": "<button class='button-save btn btn-default'>Zapisz</button>"
                },{
                    "targets": -3,
                    "data": null,
                    "defaultContent": "" +
                    "<div class='form-group modifydate' >" +
                    "<div class='input-group date form_time col-md-5' data-date='' data-date-format=hh:ii data-link-field='dtp_input3' data-link-format='hh:ii'>"+
                    "<input id='register_start' class='form-control' size='16' type='text' value='' readonly>"+
                    "<span class='input-group-addon'><span class='glyphicon glyphicon-remove'></span></span>"+
                    "<span class='input-group-addon'><span class='glyphicon glyphicon-time'></span></span>"+
                    "</div>"+
                    "<input type='hidden' id='dtp_input3' value='' /><br/>"+
                    "</div>"+

                    "<div class='form-group modifydate' >" +
                    "<div class='input-group date form_time col-md-5' data-date='' data-date-format=hh:ii data-link-field='dtp_input3' data-link-format='hh:ii'>"+
                    "<input id='register_stop' class='form-control' size='16' type='text' value='' readonly style='margin-top: 0px; min-height: 34px'>"+
                    "<span class='input-group-addon'><span class='glyphicon glyphicon-remove'></span></span>"+
                    "<span class='input-group-addon'><span class='glyphicon glyphicon-time'></span></span>"+
                    "</div>"+
                    "<input type='hidden' id='dtp_input3' value='' /><br/>"+
                    "</div>"
                }]
            });
            /*
            * Event listener responsible for refreshing ajax request
            */
            $('#checkbox1').on('change', function(e) {
              myFunction();
            });
        });

        $('#datatable tbody').on('click', '.button-save', function () {
            var data = table.row( $(this).parents('tr') ).data();
            var modify_start = $(this).closest("tr").find("input[id='register_start']").val();
            var modify_stop = $(this).closest("tr").find("input[id='register_stop']").val();
            var register_start = $(this).closest("tr").find("[name='user_register_start']").text();
            var register_stop = $(this).closest("tr").find("[name='user_register_stop']").text();
            // var succes = 0;
            var id = data.id;
            var type_edit = 0;
            var validate = 1;
            if(modify_start !='' || modify_stop !='')
            {
                if(modify_start == '' || modify_stop == '')
                {
                    swal("Brak wszystkich godzin w modyfikacji")
                    validate = 0;
                }else if(modify_start >= modify_stop)
                {
                    swal("Godziny są ustawione niepoprawnie")
                    validate = 0;
                }else
                {
                    type_edit = 1;
                }
            }else if(register_start == 'Brak infromacji' || register_stop == 'Brak infromacji')
            {
                swal("Brak wszystkich godzin w modyfikacji")
                validate = 0;
            }else
            {
                validate = 1;
            }
            if(validate == 1)
            {
                $(this).attr('disabled',true);
                $.ajax({
                    type: "POST",
                    url: '{{ route('api.saveAcceptHour') }}',
                    data: {
                        "id": id,
                        "register_start": modify_start,
                        "register_stop": modify_stop,
                        "type_edit":type_edit
                    },

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response == '-1')
                        {
                            swal("Brak zarejestrowanych godzin")
                        }else
                            table.ajax.reload();
                            $("#success_div").fadeIn();
                    }
                });
            }
        });
    </script>
@endsection
